<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Official;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BrgySetting;
use App\Models\Resident;
use App\Models\User;
use App\Models\Position;
use App\Models\OfficialTerm;
use App\Models\ChatbotFaq;

class WelcomeController extends Controller
{
    
    public function index()
    {
        // 1. Fetch Settings
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

        // 3. Your Existing Queries
        $announcements = Announcement::where('status', 'published')
            ->whereNotNull('publish_at')
            ->where('publish_at', '<=', now())
            ->orderBy('publish_at', 'desc')
            ->take(5)
            ->get();

        $officials = Official::with(['resident.user', 'position', 'currentTerm.position'])
            ->whereHas('terms', function ($query) {
                $query->where('status', 'current');
            })
            ->get()
            ->sortBy(function ($official) {
                // Get the title and convert to lowercase for easy matching
                $title = strtolower($official->currentTerm->position->title ?? '');

                // Assign a sorting priority (1 is highest, appears first)
                return match (true) {
                    str_contains($title, 'punong') || str_contains($title, 'captain') => 1,
                    str_contains($title, 'kagawad') => 2,
                    str_contains($title, 'sk') || str_contains($title, 'kabataan') => 3,
                    str_contains($title, 'secretary') => 4,
                    str_contains($title, 'treasurer') => 5,
                    default => 99, // Any other positions go to the bottom
                };
            })
            ->values();

        // --- FETCH CHATBOT FAQS ---
        $chatFaqs = ChatbotFaq::all()->mapWithKeys(function ($faq) {
            return [
                // Force keyword to lowercase so JS matching never fails
                strtolower($faq->keyword) => [
                    'auth'  => (string) $faq->response_auth,
                    'guest' => (string) $faq->response_guest,
                ]
            ];
        })->toArray(); // Convert to a plain array BEFORE adding static keys

        // Now this will merge perfectly into the JSON object
        $chatFaqs['hello'] = "Welcome to our Barangay Online Portal! How can I help you today?";

        // 4. Return View with ALL data
        return view('welcome', compact(
            'settings', 
            'services', 
            'howItWorksSteps', 
            'announcements', 
            'officials',
            'chatFaqs' // <-- Pass it to the view here!
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