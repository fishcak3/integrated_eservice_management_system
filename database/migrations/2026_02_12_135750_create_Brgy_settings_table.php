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
        // CHANGE THIS: Use lowercase 'brgy_settings'
        Schema::create('brgy_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // e.g., 'barangay_name'
            $table->text('value')->nullable(); // e.g., 'Barangay San Juan'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brgy_settings');
    }
};