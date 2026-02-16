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

            // Relationships
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('document_type_id')->constrained()->onDelete('cascade');

            $table->string('purpose'); // e.g., "For Employment"
            $table->enum('status', ['pending', 'processing', 'ready_for_pickup', 'completed', 'rejected'])->default('pending');
            $table->text('remarks')->nullable(); // Admin notes (e.g., "Missing ID")
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
