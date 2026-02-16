<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BrgySetting;
use App\Models\Official;
use Illuminate\Support\Facades\Storage;

class BrgySettingsController extends Controller
{
    public function index()
    {

        $currentCaptain = $this->getOfficialNameByPosition('Barangay Captain');
        $currentSecretary = $this->getOfficialNameByPosition('Barangay Secretary');
        $currentTreasurer = $this->getOfficialNameByPosition('Barangay Treasurer');

        return view('userdashboard.forAdmin.brgy_setting_mgt.index', [
            'barangay_name' => BrgySetting::get('barangay_name'),
            'municipality' => BrgySetting::get('municipality'),
            'address'       => BrgySetting::get('address'),
            'contact_phone' => BrgySetting::get('contact_phone'),
            'office_email' => BrgySetting::get('office_email'),
            'logo' => BrgySetting::get('barangay_logo'),
            'captain_name'   => $currentCaptain,
            'secretary_name' => $currentSecretary,
            'treasurer_name' => $currentTreasurer,
        ]);
    }

private function getOfficialNameByPosition($title)
    {
        // Find the active official with a position matching the title
        $official = Official::with('resident')
            ->where('is_active', true)
            ->whereHas('position', function($query) use ($title) {
                $query->where('title', 'LIKE', "%{$title}%"); 
            })
            ->latest()
            ->first();

        // Return formatted name or a default text
        if ($official && $official->resident) {
            return 'Hon. ' . $official->resident->fname . ' ' . $official->resident->lname;
        }

        return 'Vacant Position';
    }

    public function update(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'barangay_name' => 'required|string|max:255',
            'municipality' => 'required|string|max:255',
            'address'       => 'nullable|string|max:500',
            'contact_phone' => 'nullable|string|max:20',
            'office_email' => 'nullable|email|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'signatory_captain' => 'nullable|string|max:255',
            'signatory_secretary' => 'nullable|string|max:255',
        ]);

        // 2. Handle Text Fields
        BrgySetting::set('barangay_name', $request->barangay_name);
        BrgySetting::set('municipality', $request->municipality);
        BrgySetting::set('address', $request->address);
        BrgySetting::set('contact_phone', $request->contact_phone);
        BrgySetting::set('office_email', $request->office_email);

        // 3. Handle File Upload (Logo)
        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            $oldLogo = BrgySetting::get('barangay_logo');
            if ($oldLogo) {
                Storage::disk('public')->delete($oldLogo);
            }

            // Store new logo
            $path = $request->file('logo')->store('logos', 'public');
            BrgySetting::set('barangay_logo', $path);
        }

        // 4. Redirect back with success message
        return redirect()->route('settings.index', ['tab' => 'general'])
            ->with('status', 'Settings updated successfully!');
    }
}