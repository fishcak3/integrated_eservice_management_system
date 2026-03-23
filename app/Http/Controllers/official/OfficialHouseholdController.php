<?php

namespace App\Http\Controllers\official;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Resident;
use App\Models\Household;

class OfficialHouseholdController extends Controller
{
    public function index(Request $request)
    {
        $households = Household::getHouseholdsPaginated($request->all());

        return view('userdashboard.forOfficial.resident_mgt.house_hold.house_hold', compact('households'));
    }

    public function show($id)
    {
        $household = Household::with('members')->findOrFail($id);
        
        return view('userdashboard.forOfficial.resident_mgt.house_hold.show_house_hold', compact('household'));
    }
}
