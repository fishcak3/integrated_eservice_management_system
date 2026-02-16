<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\ResidentsController;
use App\Http\Controllers\Admin\OfficialController as AdminOfficialController;
use App\Http\Controllers\Admin\PositionController;
use App\Http\Controllers\Admin\RequestController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Admin\BrgySettingsController;

//use App\Http\Controllers\Official\OfficialController;

use App\Http\Controllers\Resident\ResidentDashboardController;
use App\Http\Controllers\Resident\ResidentRequestController;


Route::get('/', function () {
    return view('welcome');
})->name('home');

// Admins Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/residents/search', [UserManagementController::class, 'searchResidents'])->name('residents.search');

    Route::resource('users', UserManagementController::class);

    Route::get('/officials/former', [AdminOfficialController::class, 'former'])->name('officials.former');
    
    // Position Management
    Route::prefix('officials/positionsMgt')->group(function() {
         Route::get('/', [PositionController::class, 'index'])->name('positions.posIndex');
         Route::get('/create', [PositionController::class, 'create'])->name('positions.posCreate');
         Route::get('/edit/{position}', [PositionController::class, 'edit'])->name('positions.posEdit');
         Route::put('/{position}', [PositionController::class, 'update'])->name('positions.update');
         Route::delete('/{position}', [PositionController::class, 'destroy'])->name('positions.destroy');
         Route::post('/', [PositionController::class, 'store'])->name('positions.store');
    });

    Route::resource('officials', AdminOfficialController::class);

    Route::get('/residents/household', [ResidentsController::class, 'households'])->name('residents.household');
    Route::get('/residents/household/view/{id}', [ResidentsController::class, 'showHousehold'])->name('residents.household.show');
    Route::resource('residents', ResidentsController::class);

    Route::resource('requests', RequestController::class)->names('admin.requests');
    Route::patch('/requests/{documentRequest}/status', [RequestController::class, 'updateStatus'])->name('requests.update-status');

    Route::get('/complaints/create', [RequestController::class, 'complaintCreate'])->name('complaints.create');
    Route::post('/complaints', [RequestController::class, 'complaintStore'])->name('complaints.store');
    Route::get('/complaints/{id}', [RequestController::class, 'complaintShow'])->name('complaints.show');
    Route::patch('/complaints/{id}/status', [RequestController::class, 'complaintUpdateStatus'])->name('complaints.update-status');

    Route::resource('announcements', AnnouncementController::class)->names('announcements');

    Route::get('/systemSettings', [BrgySettingsController::class, 'index'])->name('settings.index');
    Route::post('/systemSettings', [BrgySettingsController::class, 'update'])->name('settings.update'); // Add this
});


// Officials Routes
//Route::middleware(['auth', 'role:official'])->prefix('official')->group(function () {

        //Route::get('/dashboard', [OfficialController::class, 'dashboard'])->name('official.dashboard');

//});


// Residents Routes
Route::middleware(['auth', 'role:resident'])->prefix('resident') ->group(function () {

        Route::get('/dashboard', [ResidentDashboardController::class, 'dashboard'])->name('resident.dashboard');

        Route::resource('requests', ResidentRequestController::class)->names('resident.requests');;
});

require __DIR__.'/settings.php';
