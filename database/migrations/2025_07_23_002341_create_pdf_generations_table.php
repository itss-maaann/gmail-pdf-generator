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
        Schema::create('pdf_generations', function (Blueprint $table) {
            $table->uuid('id')->primary(); // unique job ID
            $table->string('from_email');
            $table->string('to_email');
            $table->string('status')->default('pending'); // pending | completed | failed
            $table->string('file_path')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_generations');
    }
};
