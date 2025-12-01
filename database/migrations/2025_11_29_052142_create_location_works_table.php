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
        Schema::create('resume_location_works', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->references('id')->on('resumes')->onDelete('cascade');
            $table->string('location')->nullable();
            $table->json('other_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_works');
    }
};
