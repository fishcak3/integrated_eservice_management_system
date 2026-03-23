<?php

namespace App\Http\Controllers\official;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Resident;
use App\Models\Household;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use App\Models\Announcement;

class OfficialDashboardController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        
        // Calculate household stats first
        $totalHouseholds = Household::count();
        $totalMembersInHouseholds = Resident::whereNotNull('household_id')->count();
        $avgMembers = $totalHouseholds > 0 ? round($totalMembersInHouseholds / $totalHouseholds, 1) : 0;

        // 1. High-level Detailed Statistics
        $stats = [
            'residents' => [
                'total' => Resident::count(),
                'active' => Resident::where('status', 'active')->count(),
                'pending' => Resident::where('status', 'pending')->count(),
                'transferred' => Resident::where('status', 'transferred')->count(),
                'deceased' => Resident::where('status', 'deceased')->count(),
            ],
            'households' => [
                'total' => $totalHouseholds,
                'total_members' => $totalMembersInHouseholds,
                'avg_members' => $avgMembers,
            ],
            'requests' => [
                'total' => DocumentRequest::count(),
                'pending' => DocumentRequest::where('status', 'pending')->count(),
                'processing' => DocumentRequest::where('status', 'processing')->count(),
                'ready' => DocumentRequest::where('status', 'ready_for_pickup')->count(),
            ],
            'complaints' => [
                'active' => ComplaintRequest::whereNotIn('status', ['resolved', 'dismissed'])->count(),
                'pending' => ComplaintRequest::where('status', 'pending')->count(),
                'processing' => ComplaintRequest::where('status', 'processing')->count(),
                'scheduled' => ComplaintRequest::where('status', 'hearing_scheduled')->count(),
            ],
        ];
        
        // 2. Tasks Assigned Specifically to this Official
        $assignedRequests = DocumentRequest::with('documentType')
            ->where('assigned_official_id', $user->id)
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->take(5)
            ->get();

        $assignedComplaints = ComplaintRequest::with('type')
            ->where('assigned_official_id', $user->id)
            ->whereIn('status', ['pending', 'processing', 'hearing_scheduled']) // Adjust based on your schema
            ->latest()
            ->take(5)
            ->get();

        // 3. Recent Announcements
        $recentAnnouncements = Announcement::active()
            ->latest('publish_at')
            ->take(4)
            ->get();

        return view('userdashboard.forOfficial.dashboard', compact(
            'stats', 
            'assignedRequests', 
            'assignedComplaints', 
            'recentAnnouncements'
        ));
    }
}