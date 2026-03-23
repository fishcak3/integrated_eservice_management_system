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
        Schema::create('document_requests', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique(); // e.g., DOC-2023-001
            
            $table->string('requestor_name')->nullable();    // Full Name
            $table->string('requestor_phone')->nullable();   // Contact Number
            $table->string('requestor_address')->nullable(); // Address (since they might not be in the system)

            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('resident_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');
            $table->foreignId('assigned_official_id')->nullable()->constrained('users')->onDelete('set null'); 
            $table->enum('mode_of_request', ['online', 'walk-in'])->default('online');

            $table->string('purpose'); // e.g., "For Employment"
            $table->enum('status', ['pending', 'processing', 'pending_e_signature', 'ready_for_pickup', 'completed', 'rejected'])->default('pending');
            $table->text('remarks')->nullable(); // Admin notes (e.g., "Missing ID")
            
            $table->boolean('is_e_signed')->default(false);
            $table->timestamp('approved_at')->nullable();

            $table->string('control_number')->nullable();
            $table->string('validity_period')->nullable();
            $table->string('ordinance_number')->nullable();
            $table->string('printed_name')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_requests');
    }
};
