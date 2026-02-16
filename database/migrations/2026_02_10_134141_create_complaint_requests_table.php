<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaint_requests', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique(); // e.g., CASE-2023-888

            // Relationships
            $table->foreignId('complainant_id')->nullable()->constrained('users')->onDelete('cascade');
        
            // ADD: These columns store info if it's a walk-in guest
            $table->string('complaint_walkin_name')->nullable();
            $table->string('complaint_walkin_phone')->nullable();
            $table->string('complaint_walkin_address')->nullable();
            $table->foreignId('complaint_type_id')->constrained()->onDelete('cascade');

            $table->string('respondent_name')->nullable(); // Who they are complaining about
            $table->date('incident_date');
            $table->text('incident_details'); // The narrative
            $table->string('location'); // Where it happened
            $table->enum('status', ['pending', 'under_investigation', 'hearing_scheduled', 'resolved', 'dismissed'])->default('pending');
            
            $table->text('resolution_notes')->nullable(); // Final outcome
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaint_requests');
    }
};
