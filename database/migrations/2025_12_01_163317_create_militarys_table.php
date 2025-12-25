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
        Schema::create('militarys', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained('users')->onDelete('cascade');
            $table->string('id_card')->nullable();
            $table->integer('type')->comment('ประเภทเอกสาร สด. เช่น 43, 8, 35');
            $table->string('result')->nullable()->comment('ผลการคัดเลือก เช่น ใบดำ, ใบแดง, ผ่อนผัน, ยกเว้น');
            $table->string('reason_for_exemption',50)->nullable()->comment('เหตุผลที่ได้รับการยกเว้น');
            $table->integer('category')->nullable()->comment('จำพวก (1-4) ตามผลการตรวจร่างกาย สด.43');
            $table->date('date_to_army')->nullable()->comment('วันที่ต้องรายงานตัวเข้าประจำการ (สำหรับใบแดง)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('militarys');
    }
};
