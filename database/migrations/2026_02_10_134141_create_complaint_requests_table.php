<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the Main Complaint Table
        Schema::create('complaint_requests', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique(); // e.g., CASE-2026-888
            
            // --- COMPLAINANT INFO ---
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->foreignId('resident_id')->nullable()->constrained('residents')->onDelete('set null');
            $table->string('complainant_name')->nullable();    
            $table->string('complainant_phone')->nullable();   
            $table->text('complainant_address')->nullable(); 
            
            // --- RESPONDENT INFO ---
            $table->string('respondent_name');
            $table->foreignId('respondent_user_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->foreignId('respondent_resident_id')->nullable()->constrained('residents')->onDelete('set null'); 

            // --- COMPLAINT DETAILS ---
            $table->foreignId('complaint_type_id')->constrained()->onDelete('cascade');
            $table->enum('mode_of_request', ['online', 'walk-in'])->default('online')->index();
            $table->dateTime('incident_at'); 
            $table->string('location'); 
            $table->text('incident_details');
            
            // --- STATUS & ASSIGNMENT ---
            $table->string('status')->default('pending');
            $table->foreignId('assigned_official_id')->nullable()->constrained('users')->onDelete('set null'); 

            // --- NOTES & RESOLUTION ---
            $table->text('admin_remarks')->nullable(); 
            $table->text('investigation_notes')->nullable(); 
            $table->timestamp('hearing_date')->nullable(); 
            $table->text('resolution_notes')->nullable(); 
            $table->enum('resolution', ['founded', 'unfounded', 'settled', 'dismissed'])->nullable(); 

            // Standard Laravel Timestamps & Soft Deletes
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Create the Status History Log Table
        Schema::create('complaint_status_histories', function (Blueprint $table) {
            $table->id();
            
            // The crucial link back to the main complaint!
            $table->foreignId('complaint_request_id')->constrained('complaint_requests')->onDelete('cascade');
            
            $table->string('old_status')->nullable(); 
            $table->string('new_status');
            $table->text('remarks')->nullable(); // The admin's reason for the change
            $table->foreignId('changed_by_id')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop them in reverse order to avoid foreign key constraint errors
        Schema::dropIfExists('complaint_status_histories');
        Schema::dropIfExists('complaint_requests');
    }
};