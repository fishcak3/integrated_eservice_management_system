<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            
            // Content
            $table->string('title');
            $table->string('slug')->unique(); // For SEO-friendly URLs (e.g., /announcements/water-interruption)
            $table->longText('content'); // Using longText for rich text editors
            $table->string('cover_image')->nullable(); // Path to an image file
            
            // Meta & Status
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->enum('priority', ['normal', 'high', 'emergency'])->default('normal'); // 'emergency' can trigger red badges
            $table->boolean('is_pinned')->default(false); // To stick important posts to the top
            
            // Scheduling
            $table->timestamp('published_at')->nullable(); // Schedule posts for the future
            
            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // The admin who posted it

            $table->timestamps();
            $table->softDeletes(); // Allows restoring deleted announcements
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};