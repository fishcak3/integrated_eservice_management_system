<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Resident;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use App\Models\User;
use App\Models\OfficialTerm;
use App\Models\Announcement;
use Illuminate\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        // 1. TOP SUMMARY CARDS (Cached for 10 minutes / 600 seconds)
        $stats = Cache::remember('admin_dashboard_stats', 600, function () use ($today) {
            return [
                'residents' => [
                    'total'       => Resident::count(),
                    'active'      => Resident::where('status', 'active')->count(),
                    'pending'     => Resident::where('status', 'pending')->count(),
                    'transferred' => Resident::where('status', 'transferred')->count(),
                    'deceased'    => Resident::where('status', 'deceased')->count(),
                ],
                'users' => [
                    'total'     => User::count(),
                    'verified'  => User::whereNotNull('email_verified_at')->count(),
                    'pending'   => User::whereNull('email_verified_at')->count(),
                    'admins'    => User::where('role', 'admin')->count(), 
                    'officials' => User::where('role', 'official')->count(),
                ],
                'documents' => [
                    'total'           => DocumentRequest::count(),
                    'pending'         => DocumentRequest::where('status', 'pending')->count(),
                    'processing'      => DocumentRequest::where('status', 'processing')->count(),
                    'ready'           => DocumentRequest::where('status', 'ready_for_pickup')->count(),
                    'completed_today' => DocumentRequest::where('status', 'completed')
                                            ->whereDate('updated_at', $today)->count(),
                ],
                'complaints' => [
                    'total'           => ComplaintRequest::count(),
                    'pending'         => ComplaintRequest::where('status', 'pending')->count(),
                    'investigating'   => ComplaintRequest::where('status', 'under_investigation')->count(),
                    'resolved'        => ComplaintRequest::where('status', 'resolved')->count(),
                    'high_severity'   => ComplaintRequest::whereHas('type', function($query) {
                                            $query->whereIn('severity_level', ['high', 'critical']);
                                         })->count(),
                ],
            ];
        });

        // 2. DEMOGRAPHICS (Cached for 60 minutes)
        $demographics = Cache::remember('admin_dashboard_demographics', 3600, function () use ($today) {
            return [
                'age' => [
                    'children' => Resident::whereBetween('birthdate', [$today->copy()->subYears(12), $today])->count(),
                    'youth'    => Resident::whereBetween('birthdate', [$today->copy()->subYears(17), $today->copy()->subYears(13)])->count(),
                    'adults'   => Resident::whereBetween('birthdate', [$today->copy()->subYears(59), $today->copy()->subYears(18)])->count(),
                    'seniors'  => Resident::where('birthdate', '<=', $today->copy()->subYears(60))->count(),
                ],
                'sectoral' => [
                    'solo_parent' => Resident::where('solo_parent', true)->count(),
                    'ofw'         => Resident::where('ofw', true)->count(), 
                    'pwd'         => Resident::where('is_pwd', true)->count(),
                    '4ps'         => Resident::where('is_4ps_grantee', true)->count(),
                    'unemployed'  => Resident::where('unemployed', true)->count(),
                    'osy'         => Resident::where('out_of_school_children', true)->count(),
                ],
            ];
        });

        // 3. FINANCIALS & ANNOUNCEMENTS (Cached for 10 minutes)
        // 3. FINANCIALS (Keep cached for performance)
        $financials = Cache::remember('admin_dashboard_financials', 600, function () use ($today) {
            $topDoc = DocumentRequest::select('document_type_id', DB::raw('count(*) as total'))
                ->groupBy('document_type_id')
                ->orderByDesc('total')
                ->first();
                
            $estRevenue = DocumentRequest::whereMonth('document_requests.created_at', $today->month)
                ->join('document_types', 'document_requests.document_type_id', '=', 'document_types.id')
                ->sum('document_types.fee');

            return [
                'est_revenue'  => $estRevenue, 
                'top_document' => $topDoc && $topDoc->documentType ? $topDoc->documentType->name : 'N/A',
            ];
        });

        // --- FIXED: Removed Cache here so counts are always 100% accurate in real-time ---
        $announcements = [
            'published' => Announcement::where('status', 'published')->count(),
            'draft'     => Announcement::where('status', 'draft')->count(),
            'archived'  => Announcement::where('status', 'archived')->count(),
        ];

        // --- FIXED: Calendar logic to handle NULL publish_at dates and removed cache ---
        $startOfMonth = $today->copy()->startOfMonth();
        $endOfMonth = $today->copy()->endOfMonth();

        // Fetch announcements. If publish_at is null (drafts), check created_at instead.
        $monthlyAnnouncements = Announcement::where(function($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('publish_at', [$startOfMonth, $endOfMonth])
                      ->orWhere(function($q) use ($startOfMonth, $endOfMonth) {
                          $q->whereNull('publish_at')
                            ->whereBetween('created_at', [$startOfMonth, $endOfMonth]);
                      });
            })
            ->get()
            ->groupBy(function($announcement) {
                // Group by publish_at, but if it's null, fall back to created_at
                $targetDate = $announcement->publish_at ?? $announcement->created_at;
                return \Carbon\Carbon::parse($targetDate)->format('Y-m-d');
            });

        $calendarDays = [];
        $startDayOfWeek = $startOfMonth->dayOfWeek; // 0 (Sun) to 6 (Sat)
        $daysInMonth = $startOfMonth->daysInMonth;

        // Pad the empty days at the start of the month for the UI grid
        for ($i = 0; $i < $startDayOfWeek; $i++) {
            $calendarDays[] = ['day' => '', 'date' => null, 'events' => collect()];
        }

        // Fill in the actual days of the month
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateString = $startOfMonth->copy()->addDays($day - 1)->format('Y-m-d');
            $events = $monthlyAnnouncements->get($dateString, collect());
            
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateString,
                'events' => $events
            ];
        }


        // 4. ALERTS (NOT Cached - Real-time)
        $alerts = [];
        $staleComplaints = ComplaintRequest::where('status', 'pending')
            ->where('created_at', '<', $today->copy()->subDays(7))->count();
        if ($staleComplaints > 0) {
            $alerts[] = ['message' => "⚠ There are {$staleComplaints} pending complaints older than 7 days."];
        }

        $staleDocs = DocumentRequest::where('status', 'pending')
            ->where('created_at', '<', $today->copy()->subDays(3))->count();
        if ($staleDocs > 0) {
            $alerts[] = ['message' => "⚠ There are {$staleDocs} document requests waiting more than 3 days."];
        }

        // 5. TABLES & OFFICIALS (NOT Cached - Real-time)
        $recentRequests = DocumentRequest::with('documentType')
            ->latest()
            ->take(5)
            ->get();

        $recentComplaints = ComplaintRequest::with('type')
            ->latest()
            ->take(5)
            ->get();

        $currentOfficials = OfficialTerm::with(['official.resident', 'position'])
            ->where('is_active', true)
            ->where('status', 'current')
            ->get();

        $activityLogs = \Spatie\Activitylog\Models\Activity::with('causer')
            ->latest()
            ->take(5) 
            ->get();

        return view('userdashboard.forAdmin.dashboard', compact(
            'stats',
            'demographics',
            'financials',
            'announcements',
            'calendarDays', // Added this!
            'alerts',
            'recentRequests',
            'recentComplaints',
            'currentOfficials',
            'activityLogs'
        ));
    }
}