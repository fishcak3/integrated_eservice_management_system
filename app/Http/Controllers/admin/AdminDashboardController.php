<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        // 1. Resident Statistics
        $totalResidents = Resident::where('status', 'is_active')->count();
        $totalVoters = Resident::where('status', 'is_active')->where('voter', true)->count();
        
        // Sectoral counts
        $sectoralStats = [
            'seniors' => Resident::where('senior_citizen', true)->count(),
            'pwd' => Resident::where('is_pwd', true)->count(),
            'solo_parents' => Resident::where('solo_parent', true)->count(),
            '4ps' => Resident::where('is_4ps', true)->count(),
        ];

        // 2. Document Request Statuses
        $pendingDocs = DocumentRequest::where('status', 'pending')->count();
        $todaysDocs = DocumentRequest::whereDate('created_at', today())->count();

        // 3. Active Complaints Count (Pending, Investigation, or Hearing)
        $activeComplaints = ComplaintRequest::whereIn('status', ['pending', 'under_investigation', 'hearing_scheduled'])->count();

        // 4. Recent Requests Table Data
        $recentRequests = DocumentRequest::with('documentType')
            ->latest()
            ->take(5)
            ->get();

        $recentComplaints = ComplaintRequest::latest()
            ->take(5)
            ->get();

        return view('userdashboard.forAdmin.dashboard', compact(
            'totalResidents', 
            'totalVoters', 
            'sectoralStats', 
            'pendingDocs', 
            'todaysDocs', 
            'activeComplaints',
            'recentRequests',
            'recentComplaints' 
        ));
    }
}