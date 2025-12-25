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
        Schema::create('resume_work_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignID('resume_id')->constrained('resumes')->onDelete('cascade'); // (แนะนำสำหรับตารางลูก)
            $table->string('company')->nullable(); // บริษัท
            $table->string('position')->nullable(); // ตำแหน่ง
            $table->string('start')->nullable(); // ช่วงเวลา
            $table->string('last')->nullable(); // ช่วงเวลา
            $table->string('salary')->nullable(); // เงินเดือน (ใช้ string ตามการตกลงเพื่อให้ง่ายต่อการจัดการ JSON)
            $table->string('reason_for_leaving')->nullable(); //เหตุผลที่ลาออก
            $table->text('details')->nullable(); // รายละเอียด (ใช้ text สำหรับ textarea)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resume_work_experiences');
    }
};
