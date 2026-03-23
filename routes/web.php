<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\Common\SearchController;
use App\Models\ChatMessage;

use App\Http\Controllers\admin\AdminDashboardController;
use App\Http\Controllers\admin\UserManagementController;
use App\Http\Controllers\admin\ResidentsController;
use App\Http\Controllers\admin\HouseholdController;
use App\Http\Controllers\admin\ResidentImportExportController;
use App\Http\Controllers\admin\DocumentRequestController;
use App\Http\Controllers\admin\ComplaintRequestController;
use App\Http\Controllers\admin\OfficialController as AdminOfficialController;
use App\Http\Controllers\admin\PositionController;
use App\Http\Controllers\admin\ArchivedAnnouncementController;
use App\Http\Controllers\admin\AnnouncementController;
use App\Http\Controllers\admin\BrgySettingsController;
use App\Http\Controllers\admin\ActivityLogController;
use App\Livewire\Admin\ChatbotFaqManager;

use App\Http\Controllers\official\OfficialDashboardController;
use App\Http\Controllers\official\OfficialRequestController;
use App\Http\Controllers\official\OfficialComplaintController;
use App\Http\Controllers\official\OfficialAnnouncementController;
use App\Http\Controllers\official\OfficialArchivedController;
use App\Http\Controllers\official\OfficialResidentController;
use App\Http\Controllers\official\OfficialImportExportController;
use App\Http\Controllers\official\OfficialHouseholdController;

use App\Http\Controllers\resident\ResidentDashboardController;
use App\Http\Controllers\resident\ResidentRequestController;
use App\Http\Controllers\resident\ResidentComplaintController;
use App\Http\Controllers\resident\ResidentAnnouncementController;


Route::get('/', [WelcomeController::class, 'index'])->name('welcome');
Route::get('/announcements/{announcement}', [WelcomeController::class, 'showAnnouncement'])->name('public.announcements.show');
Route::view('/privacy-policy', 'privacy')->name('privacy');
Route::view('/terms-of-service', 'terms')->name('terms');

Route::get('/forgot-password', function () {
    return view('pages.auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {

    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
                
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('pages.auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {

    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user) use ($request) {
            $user->forceFill([
                'password' => Hash::make($request->password),
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
        
})->middleware('guest')->name('password.update');

Route::get('/email/verify', function () {
    return view('pages.auth.verify-email');
})->middleware('auth')->name('verification.notice'); 

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect('/dashboard'); 
})->middleware(['auth', 'signed'])->name('verification.verify'); 

Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();

    return back()->with('status', 'verification-link-sent'); 
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

Route::get('/dashboard', function () {
    $user = Auth::user();

    return match($user->role) {
        'admin'    => redirect()->route('admin.dashboard'),
        'resident' => redirect()->route('resident.dashboard'),
        'official' => redirect()->route('official.dashboard'), 
        default    => abort(403, 'Unauthorized action.'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/search/users', [SearchController::class, 'users'])->name('users.search');
    Route::get('/search/residents', [SearchController::class, 'residents'])->name('residents.search');
    Route::get('/search/households', [SearchController::class, 'households'])->name('households.search');
});

// Admins Routes
Route::middleware(['auth', 'verified', 'role:admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/messages', \App\Livewire\Admin\ChatManager::class)->name('admin.chat');

    Route::get('/users/search', [UserManagementController::class, 'search'])->name('users.search');
    Route::get('/users/{user}/document', [UserManagementController::class, 'viewDocument'])->name('users.document');
    Route::patch('/users/{user}/verify', [UserManagementController::class, 'verify'])->name('users.verify');
    Route::patch('/users/{user}/reject', [UserManagementController::class, 'reject'])->name('users.reject');
    Route::view('/admin/users/verifications', 'userdashboard.forAdmin.user_mgt.verifications.index')->name('users.verifications');
    Route::resource('users', UserManagementController::class);

    Route::get('/officials/former', [AdminOfficialController::class, 'former'])->name('officials.former');
    Route::get('/officials/former/{id}', [AdminOfficialController::class, 'showFormer'])->name('officials.showFormer');
    
    // Position Management
    Route::prefix('officials/positionsMgt')->group(function() {
         Route::get('/', [PositionController::class, 'index'])->name('positions.posIndex');
         Route::get('/create', [PositionController::class, 'create'])->name('positions.posCreate');
         Route::get('/edit/{position}', [PositionController::class, 'edit'])->name('positions.posEdit');
         Route::put('/{position}', [PositionController::class, 'update'])->name('positions.update');
         Route::delete('/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
         Route::post('/', [PositionController::class, 'store'])->name('positions.store');
    });
    Route::delete('/officials/{official}', [AdminOfficialController::class, 'destroy'])->name('officials.destroy');         
    Route::resource('officials', AdminOfficialController::class);

    Route::view('/residents/requests', 'userdashboard.forAdmin.resident_mgt.requests')->name('admin.residents.requests');

    Route::post('/residents/import', [ResidentImportExportController::class, 'import'])->name('admin.residents.import'); 
    Route::get('/residents/export-csv', [ResidentImportExportController::class, 'exportCsv'])->name('admin.residents.export.csv');
    Route::get('/residents/export-pdf', [ResidentImportExportController::class, 'exportPdf'])->name('admin.residents.export.pdf');

    Route::get('/residents/household', [HouseholdController::class, 'index'])->name('admin.residents.household');
    Route::get('/residents/household/num-{id}', [HouseholdController::class, 'show'])->name('admin.residents.household.show');
    Route::delete('/residents/household/del-{id}', [HouseholdController::class, 'destroy'])->name('admin.residents.household.destroy');
    
    Route::get('/residents/res-{resident}', [ResidentsController::class, 'show'])->name('admin.residents.show');
    Route::get('/residents/edt-{resident}', [ResidentsController::class, 'edit'])->name('admin.residents.edit');
    Route::resource('residents', ResidentsController::class)->names('admin.residents')->except(['show', 'edit']);
    
    // --- Document Request Routes ---
    Route::prefix('documents')->name('admin.documents.')->group(function () {
        // Resource-like routes for Documents
        Route::get('/', [DocumentRequestController::class, 'index'])->name('index');
        Route::get('/create', [DocumentRequestController::class, 'create'])->name('create');
        Route::post('/', [DocumentRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [DocumentRequestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [DocumentRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [DocumentRequestController::class, 'update'])->name('update');
        
        // Custom Actions
        Route::get('/{id}/process', [DocumentRequestController::class, 'process'])->name('process');
        Route::patch('/{id}/approve-sign', [DocumentRequestController::class, 'approveSign'])->name('approve-sign');
        Route::post('/{id}/request-e-sign', [DocumentRequestController::class, 'requestESign'])->name('request-e-sign');
        Route::get('/{id}/preview', [DocumentRequestController::class, 'preview'])->name('preview');
        Route::patch('/{documentRequest}/status', [DocumentRequestController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/assign', [DocumentRequestController::class, 'assignOfficial'])->name('assign');
    });

    // --- Complaint Request Routes ---
    Route::prefix('complaints')->name('admin.complaints.')->group(function () {
        Route::get('/', [ComplaintRequestController::class, 'index'])->name('index');
        Route::get('/create', [ComplaintRequestController::class, 'create'])->name('create');
        Route::post('/', [ComplaintRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [ComplaintRequestController::class, 'show'])->name('show');
        
        // Custom Actions
        Route::patch('/{id}/status', [ComplaintRequestController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/assign', [ComplaintRequestController::class, 'assignOfficial'])->name('assign');
    });

    Route::get('/announcements/archived', [ArchivedAnnouncementController::class, 'archived'])->name('admin.announcements.archived');
    Route::get('/announcements/archived/{announcement}', [ArchivedAnnouncementController::class, 'archivedShow'])->name('admin.announcements.archived.show');
    Route::get('/announcements/archived/{announcement}/edit', [ArchivedAnnouncementController::class, 'archivedEdit'])->name('admin.announcements.archived.edit');
    Route::patch('/announcements/{announcement}/update-status', [ArchivedAnnouncementController::class, 'updateStatus'])->name('admin.announcements.update-status');
    
    Route::resource('announcements', AnnouncementController::class)->names('admin.announcements');

    Route::get('/systemSettings', [BrgySettingsController::class, 'index'])->name('settings.index');
    Route::post('/systemSettings', [BrgySettingsController::class, 'update'])->name('settings.update'); 

    Route::get('/systemSettings/backup', [BrgySettingsController::class, 'backup'])->name('settings.backup');
    Route::post('/systemSettings/backup/generate', [BrgySettingsController::class, 'generateBackup'])->name('settings.backup.generate');
    Route::get('/systemSettings/backup/download/{file}', [BrgySettingsController::class, 'downloadBackup'])->name('settings.backup.download');
    Route::delete('/systemSettings/backup/delete/{file}', [BrgySettingsController::class, 'deleteBackup'])->name('settings.backup.delete');

    Route::post('/settings/cache/clear', [BrgySettingsController::class, 'clearCache'])->name('settings.cache.clear');
    Route::post('/settings/views/clear', [BrgySettingsController::class, 'clearViews'])->name('settings.views.clear');
    Route::post('/settings/maintenance/toggle', [BrgySettingsController::class, 'toggleMaintenance'])->name('settings.maintenance.toggle');

    Route::get('/settings/logs', [ActivityLogController::class, 'index'])->name('settings.logs');
    Route::get('/settings/logs/{activityLog}', [ActivityLogController::class, 'show'])->name('settings.logs.show');

    Route::get('/settings/requests', [BrgySettingsController::class, 'requests'])->name('settings.request');
    Route::get('/settings/requests/create', [BrgySettingsController::class, 'createRequestType'])->name('settings.request.create');
    Route::post('/settings/requests/store', [BrgySettingsController::class, 'storeRequestType'])->name('settings.request.store');
    Route::get('/settings/requests/{type}/{id}/edit', [BrgySettingsController::class, 'editRequestType'])->name('settings.request.edit');
    Route::put('/settings/requests/{type}/{id}', [BrgySettingsController::class, 'updateRequestType'])->name('settings.request.update');

    Route::get('/admin/chatbot-faqs', ChatbotFaqManager::class)->name('admin.chatbot.faqs');
});


// Officials Routes
Route::middleware(['auth', 'verified', 'role:official'])->prefix('official')->group(function () {

    Route::get('/dashboard', [OfficialDashboardController::class, 'dashboard'])->name('official.dashboard');

    // --- Document Request Routes ---
    Route::prefix('documents')->name('official.documents.')->group(function () {
        // Resource-like routes for Documents
        Route::get('/', [OfficialRequestController::class, 'index'])->name('index');
        Route::get('/create', [OfficialRequestController::class, 'create'])->name('create');
        Route::post('/', [OfficialRequestController::class, 'store'])->name('store');
        Route::get('/{id}', [OfficialRequestController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [OfficialRequestController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OfficialRequestController::class, 'update'])->name('update');
        
        // Custom Actions
        Route::get('/{id}/process', [OfficialRequestController::class, 'process'])->name('process');
        Route::patch('/{id}/approve-sign', [OfficialRequestController::class, 'approveSign'])->name('approve-sign');
        Route::post('/{id}/request-e-sign', [OfficialRequestController::class, 'requestESign'])->name('request-e-sign');
        Route::get('/{id}/preview', [OfficialRequestController::class, 'preview'])->name('preview');
        Route::patch('/{documentRequest}/status', [OfficialRequestController::class, 'updateStatus'])->name('update-status');
        Route::post('/{id}/assign', [OfficialRequestController::class, 'assignOfficial'])->name('assign');
    });

    // --- Complaint Request Routes ---
    Route::prefix('complaints')->name('official.complaints.')->group(function () {
        Route::get('/', [OfficialComplaintController::class, 'index'])->name('index');
        Route::get('/create', [OfficialComplaintController::class, 'create'])->name('create');
        Route::post('/', [OfficialComplaintController::class, 'store'])->name('store');
        Route::get('/{id}', [OfficialComplaintController::class, 'show'])->name('show');
        
        // Custom Actions
        Route::patch('/{id}/status', [OfficialComplaintController::class, 'updateStatus'])->name('update-status');
    });

    Route::resource('/announcements', OfficialAnnouncementController::class)->names('official.announcements');
        
    Route::post('/residents/import', [OfficialImportExportController::class, 'import'])->name('official.residents.import'); 
    Route::get('/residents/export-csv', [OfficialImportExportController::class, 'exportCsv'])->name('official.residents.export.csv');
    Route::get('/residents/export-pdf', [OfficialImportExportController::class, 'exportPdf'])->name('official.residents.export.pdf');

    Route::get('/residents/household', [OfficialHouseholdController::class, 'index'])->name('official.residents.household');
    Route::get('/residents/household/hh-{id}', [OfficialHouseholdController::class, 'show'])->name('official.residents.household.show');

    Route::get('/residents/res-{resident}', [OfficialResidentController::class, 'show'])->name('official.residents.show');
    Route::get('/residents/edt-{resident}', [OfficialResidentController::class, 'edit'])->name('official.residents.edit');
    Route::resource('residents', OfficialResidentController::class)->names('official.residents')->except(['show', 'edit']);
    
});

// Residents Routes
Route::middleware(['auth', 'verified', 'role:resident'])->prefix('resident')->group(function () {

    Route::get('/dashboard', [ResidentDashboardController::class, 'dashboard'])->name('resident.dashboard');

    Route::resource('requests', ResidentRequestController::class)->names('resident.requests');
    Route::resource('complaints', ResidentComplaintController::class)->names('resident.complaints');

    Route::get('/announcements', [ResidentAnnouncementController::class, 'index'])->name('resident.announcements.index');
    Route::get('/announcements/{announcement}', [ResidentAnnouncementController::class, 'show'])->name('resident.announcements.show');
    
    // Fixed: Removed the extra /resident from the URL
    Route::get('/support-chat', \App\Livewire\Resident\LiveChat::class)->name('resident.chat');
    
    // Tip: Consider moving this closure into a Controller! Added a name for easier linking.
    Route::post('/chat-inquiries/store', function (Illuminate\Http\Request $request) {
        $request->validate(['message' => 'required|string']);

        ChatMessage::create([
            'resident_id' => auth()->id(),
            'sender_id' => auth()->id(),
            'message' => $request->message,
            'is_read_by_admin' => false,
            'is_read_by_resident' => true,
        ]);

        return response()->json(['success' => true]);
    })->name('resident.chat.store');
});

require __DIR__.'/settings.php';
