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
            $table->string('title');
            $table->string('slug')->unique(); // For SEO-friendly URLs (e.g., /announcements/water-interruption)
            $table->longText('content'); 
            $table->string('cover_image')->nullable(); 
            $table->enum('status', ['published', 'archived'])->default('published');
            $table->timestamp('publish_at')->nullable();
            $table->timestamp('expires_at')->nullable();    
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};