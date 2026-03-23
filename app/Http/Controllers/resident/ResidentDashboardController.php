<?php

namespace App\Http\Controllers\resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DocumentRequest;
use App\Models\ComplaintRequest;
use Carbon\Carbon;
use App\Models\Announcement;
use App\Models\Resident;
use App\Models\Official;
use App\Models\ChatbotFaq;

class ResidentDashboardController extends Controller
{
    public function dashboard()
    {
        $userId = auth()->id();
        
        // Fetch all for stats
        $userDocuments = DocumentRequest::where('user_id', $userId)->get();
        $userComplaints = ComplaintRequest::where('user_id', $userId)->get();

        // 1. Alerts
        $alerts = [
            ['message' => 'Welcome to your Resident Portal! Make sure your profile is updated.']
        ];

        $stats = [
            'documents' => [
                'total' => $userDocuments->count(),
                'pending' => $userDocuments->where('status', 'pending')->count(),
                'processing' => $userDocuments->where('status', 'processing')->count(),
                'ready' => $userDocuments->where('status', 'ready_for_pickup')->count(),
                'completed_today' => $userDocuments->where('status', 'completed')
                                                ->where('updated_at', '>=', now()->startOfDay())
                                                ->count(),
            ],
            'complaints' => [
                'total' => $userComplaints->count(),
                'pending' => $userComplaints->where('status', 'pending')->count(),
                'investigating' => $userComplaints->where('status', 'under_investigation')->count(),
                'resolved' => $userComplaints->where('status', 'resolved')->count(),
                'high_severity' => 0, 
            ]
        ];

        $recentAnnouncements = Announcement::where('status', 'published')
                                        ->where(function ($query) {
                                            $query->whereNull('publish_at')
                                                  ->orWhere('publish_at', '<=', now());
                                        })
                                        ->where(function ($query) {
                                            $query->whereNull('expires_at')
                                                  ->orWhere('expires_at', '>', now());
                                        })
                                        ->orderBy('publish_at', 'desc')
                                        ->take(3) 
                                        ->get();

        $recentRequests = DocumentRequest::with('documentType')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $recentComplaints = ComplaintRequest::with('type')
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        $currentOfficials = []; 

        // --- FETCH CHATBOT FAQS ---
        $chatFaqs = ChatbotFaq::all()->keyBy('keyword')->map(function ($faq) {
            return [
                'auth' => $faq->response_auth,
                'guest' => $faq->response_guest,
            ];
        });
        $chatFaqs['hello'] = "Hi there! How can I assist you with our barangay system today?";

        return view('userdashboard.forResident.dashboard', compact(
            'alerts', 
            'stats', 
            'recentAnnouncements', 
            'recentRequests', 
            'recentComplaints', 
            'currentOfficials',
            'chatFaqs' // <-- Pass it to the view here!
        ));
    }
}
