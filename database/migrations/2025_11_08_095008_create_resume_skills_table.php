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
        Schema::create('resume_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->constrained('resumes')->onDelete('cascade');
            $table->string('skill_name')->nullable(); // ชื่อทักษะหรือเครื่องมือ (เปลี่ยนจาก 'name' เพื่อความชัดเจน)
            $table->string('level')->nullable(); // ระดับความชำนาญ (สูง, กลาง, พื้นฐาน)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_skills');
    }
};
