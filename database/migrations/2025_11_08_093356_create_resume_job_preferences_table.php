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
        Schema::create('resume_job_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->constrained('resumes')->onDelete('cascade'); // เชื่อมกับตาราง employees
            $table->string('availability_date')->nullable(); // วันที่สะดวกเริ่มทำงาน (ใช้ string เพื่อความยืดหยุ่นตาม JSON)
            $table->string('expected_salary')->nullable(); // เงินเดือนที่คาดหวัง (ใช้ string เพื่อความยืดหยุ่นตาม JSON)
            $table->json('position')->nullable();
            $table->json('location')->nullable();
            $table->json('other_location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_job_preferences');
    }
};
