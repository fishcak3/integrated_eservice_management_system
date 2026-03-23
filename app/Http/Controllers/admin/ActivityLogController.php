<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use App\Models\User;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs.
     */
    public function index(Request $request)
    {
        // Eager load the causer (the user who did the action) and their resident profile
        $query = Activity::with(['causer.resident'])->latest();

        // 1. Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            
            $query->where(function ($q) use ($search) {
                // Search in the log description or event type
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
                  
                // Search by the causer's (User's) name or email
                $q->orWhereHasMorph('causer', [User::class], function ($userQuery) use ($search) {
                    $userQuery->where('email', 'like', "%{$search}%")
                              ->orWhereHas('resident', function ($resQuery) use ($search) {
                                  $resQuery->where('fname', 'like', "%{$search}%")
                                           ->orWhere('lname', 'like', "%{$search}%");
                              });
                });
            });
        }

        // 2. Event Type Filter (Created, Updated, Deleted)
        if ($request->filled('event_type')) {
            $query->where('event', $request->event_type);
        }

        // 3. Date Filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Paginate results and keep query string for pagination links
        $logs = $query->paginate(15)->withQueryString();

        return view('userdashboard.forAdmin.system_settings_mgt.logs.index', compact('logs'));
    }

    /**
     * Optional: Display a specific log entry in detail.
     * Useful if you want a dedicated page/modal for massive JSON changes.
     */
    public function show(Activity $activityLog)
    {
        $activityLog->load('causer.resident', 'subject');
        
        return view('admin.settings.logs.show', compact('activityLog'));
    }
}