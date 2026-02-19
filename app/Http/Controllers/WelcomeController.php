<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BrgySetting;

class WelcomeController extends Controller
{
public function index()
    {
        // 1. Fetch Settings (The Laravel Way)
        // ideally, use the Model: $settings = BrgySetting::pluck('value', 'key');
        // But to keep your current safety check logic:
        $settings = [];
        try {
            $settings = DB::table('brgy_settings')->pluck('value', 'key');
        } catch (\Exception $e) {
            // Table might not exist yet, ignore
        }

        // 2. Define Static Data
        $services = [
            ['icon' => 'file-text', 'title' => "Certificate Requests", 'description' => "Apply for barangay certificates, permits, and clearances online"],
            ['icon' => 'users', 'title' => "Resident Records", 'description' => "Update and manage your personal and family information"],
            ['icon' => 'bell', 'title' => "Announcements", 'description' => "Stay updated with the latest barangay news and events"],
            ['icon' => 'shield', 'title' => "Security Services", 'description' => "Report incidents and request security assistance"],
        ];

        $howItWorksSteps = [
            ['title' => "Create your Account", 'description' => "Register with your valid information and get verified as a barangay resident"],
            ['title' => "Submit your Request", 'description' => "Choose the certificate you need and fill out the required forms online"],
            ['title' => "Receive your Documents", 'description' => "Get notified when ready and download your documents or claim at the office"],
        ];

        // 3. Your Existing Queries (Announcements, Officials, etc.)
        $announcements = Announcement::where('status', 'published')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->orderBy('publish_at', 'desc')
            ->take(5)
            ->get();

        $officials = Official::where('is_active', true)->get(); 

        // 4. Return View with ALL data
        return view('welcome', compact(
            'settings', 
            'services', 
            'howItWorksSteps', 
            'announcements', 
            'officials'
        ));
    }

    public function showAnnouncement(Announcement $announcement)
    {
        // Check if it's actually published (security check)
        if($announcement->status !== 'published') {
            abort(404);
        }

        return view('announcement-details', compact('announcement'));
    }
}