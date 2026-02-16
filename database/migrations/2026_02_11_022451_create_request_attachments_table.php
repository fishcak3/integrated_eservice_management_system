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
        Schema::create('request_attachments', function (Blueprint $table) {
            $table->id();
            
            // Link to the main request
            $table->foreignId('document_request_id')->constrained()->onDelete('cascade');
            
            // File details
            $table->string('file_path'); // stored in storage/app/public/...
            $table->string('file_name')->nullable(); // original filename
            $table->string('file_type')->nullable(); // mime type (image/jpeg, pdf, etc)
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_attachments');
    }
};
