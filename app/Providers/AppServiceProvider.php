<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use App\Models\ChatbotFaq;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth; 
use App\Models\BrgySetting;
use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\ComplaintRequest;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Schema; // <-- Added this to safely check tables

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

        // 1. Share Global Settings
        try {
            $settings = Cache::rememberForever('global_brgy_settings', function () {
                return BrgySetting::pluck('value', 'key')->toArray();
            });

            // Share the entire settings array to ALL views
            View::share('settings', $settings);

            // Keep your specific global variables if you use them elsewhere
            View::share('global_logo', $settings['barangay_logo'] ?? null);
            View::share('global_sitios', json_decode($settings['sitios'] ?? '[]', true) ?? []);
            View::share('global_brgy_name', $settings['barangay_name'] ?? 'Barangay Portal');
            View::share('global_municipality', $settings['municipality'] ?? 'Unknown Municipality');
            View::share('global_province', $settings['province'] ?? 'Unknown Province');
            View::share('global_region', $settings['region'] ?? 'Unknown Region');
            View::share('global_postal_code', $settings['postal_code'] ?? '');

        } catch (\Exception $e) {
            View::share('settings', []);
            View::share('global_logo', null);
            View::share('global_sitios', []);
            View::share('global_brgy_name', 'Barangay Portal');
            View::share('global_municipality', '');
            View::share('global_province', '');
            View::share('global_region', '');
            View::share('global_postal_code', '');
        }

        // 2. Chatbot View Composer (NOW FULLY DYNAMIC)
        View::composer('partials.chatbot', function ($view) {
            try {
                // A. Get Manual FAQs
                $faqs = ChatbotFaq::all()->keyBy('keyword')->map(function ($faq) {
                    return [
                        'auth' => $faq->response_auth,
                        'guest' => $faq->response_guest,
                    ];
                })->toArray(); // <-- Converted to Array for Alpine

                // B. Get Dynamic Document Data
                $documents = [];
                if (Schema::hasTable('document_types')) {
                    $documents = DocumentType::where('is_active', true)->get()->mapWithKeys(function($doc) {
                        $fee = $doc->fee > 0 ? '₱' . number_format($doc->fee, 2) : 'Free';
                        $reqs = $doc->requirements ?: 'No specific requirements listed.';
                        
                        return [
                            strtolower($doc->name) => "The fee for a {$doc->name} is {$fee}. Requirements: {$reqs}"
                        ];
                    })->toArray(); // <-- Converted to Array for Alpine
                }

                // C. Merge them both into a single Knowledge Base
                $chatFaqs = array_merge($faqs, $documents);
                
                // D. Add Default Greetings
                $chatFaqs['hello'] = "Hi there! How can I assist you with our barangay system today?";
                $chatFaqs['hi'] = "Hello! How can I help you today?";

            } catch (\Exception $e) {
                // Safe fallback if database isn't migrated yet
                $chatFaqs = ['hello' => 'Hi! Chatbot is currently undergoing maintenance.'];
            }

            $view->with('chatFaqs', $chatFaqs);
        });

        // 3. Sidebar View Composer
        View::composer('layouts.app.sidebar', function ($view) { 
            if (Auth::check()) {
                $user = Auth::user();
                $userId = $user->id;
                $role = $user->role;

                $pendingDocsCount = 0;
                $pendingComplaintsCount = 0;

                if ($role === 'admin') {
                    $pendingDocsCount = Cache::remember('admin_pending_docs', 300, function () {
                        return DocumentRequest::where('status', 'pending')->count();
                    });
                    $pendingComplaintsCount = Cache::remember('admin_pending_complaints', 300, function () {
                        return ComplaintRequest::where('status', 'pending')->count();
                    });
                } 
                elseif ($role === 'official') {
                    $pendingDocsCount = Cache::remember('official_pending_docs_' . $userId, 300, function () use ($userId) {
                        return DocumentRequest::where('assigned_official_id', $userId)
                            ->where('status', 'pending')->count();
                    });
                    $pendingComplaintsCount = Cache::remember('official_pending_complaints_' . $userId, 300, function () use ($userId) {
                        return ComplaintRequest::where('assigned_official_id', $userId)
                            ->where('status', 'pending')->count();
                    });
                } 
                elseif ($role === 'resident') {
                    $pendingDocs = Cache::remember('resident_pending_docs_' . $userId, 300, function () use ($userId) {
                        return DocumentRequest::where('user_id', $userId)
                            ->where('status', 'pending')->count();
                    });
                    $pendingComplaints = Cache::remember('resident_pending_complaints_' . $userId, 300, function () use ($userId) {
                        return ComplaintRequest::where('user_id', $userId)
                            ->where('status', 'pending')->count();
                    });
                    
                    $view->with('pendingDocs', $pendingDocs)
                         ->with('pendingComplaints', $pendingComplaints);
                }

                $view->with('pendingDocsCount', $pendingDocsCount)
                     ->with('pendingComplaintsCount', $pendingComplaintsCount);
            }
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }
}