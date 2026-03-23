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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            // The resident who owns this conversation
            $table->foreignId('resident_id')->constrained('users')->cascadeOnDelete(); 
            
            $table->foreignId('sender_id')->nullable()->constrained('users')->cascadeOnDelete();
            
            $table->text('message');
            
            // Tracking unread statuses separately
            $table->boolean('is_read_by_admin')->default(false);
            $table->boolean('is_read_by_resident')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
