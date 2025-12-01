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
        Schema::create('resume_lang_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->references('id')->on('resumes')->onDelete('cascade'); // (แนะนำสำหรับตารางลูก)
            $table->string('language')->nullable(); // ภาษา
            $table->string('speaking')->nullable(); // ระดับการพูด
            $table->string('listening')->nullable(); // ระดับการอ่าน
            $table->string('writing')->nullable(); // ระดับการเขียน
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_lang_skills');
    }
};
