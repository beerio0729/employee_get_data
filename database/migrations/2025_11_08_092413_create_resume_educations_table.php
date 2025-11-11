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
        Schema::create('resume_educations', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->references('id')->on('resumes')->onDelete('cascade');
            $table->string('institution')->nullable();
            $table->string('degree')->nullable();
            $table->string('faculty')->nullable();
            $table->string('education_level')->nullable();
            $table->string('major')->nullable();
            $table->integer('last_year')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_educations');
    }
};
