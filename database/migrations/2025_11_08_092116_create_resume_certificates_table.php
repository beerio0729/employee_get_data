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
        Schema::create('resume_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->constrained('resumes')->onDelete('cascade'); // เชื่อมกับตาราง employees
            $table->string('name')->nullable(); // ชื่อใบประกาศ/เกียรติบัตร
            $table->string('date_obtained')->nullable(); // วันที่ได้รับ (ใช้ string เพื่อความยืดหยุ่นตาม JSON)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_certificates');
    }
};
