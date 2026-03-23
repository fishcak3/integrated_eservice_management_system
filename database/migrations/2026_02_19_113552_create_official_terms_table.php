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
        Schema::create('official_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('official_id')->constrained('officials')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->date('term_start');
            $table->date('term_end')->nullable();
            $table->enum('status', ['current','completed','resigned','removed'])->default('current');
            $table->string('election_year')->nullable(); // Example: 2023-2026
            $table->unique(['official_id', 'position_id', 'term_start'], 'official_position_term_unique'); // Prevent duplicate active same official + position
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('official_terms');
    }
};
