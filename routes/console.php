<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('announcements:archive-expired')->everyMinute();

Schedule::command('announcements:publish-scheduled')->everyMinute();

Schedule::call(function () {
    // Find all users who were verified MORE than 30 days ago, who still have a document attached
    $expiredUsers = User::whereNotNull('supporting_document')
        ->where('verification_status', 'verified')
        ->where('account_verified_at', '<', now()->subDays(30))
        ->get();

    foreach ($expiredUsers as $user) {
        // Delete the file from the secure storage
        if (Storage::disk('local')->exists($user->supporting_document)) {
            Storage::disk('local')->delete($user->supporting_document);
        }
        
        // Remove the file path from the database so the system knows it's gone
        $user->update(['supporting_document' => null]);
    }
})->daily(); // This tells Laravel to run this check once every day