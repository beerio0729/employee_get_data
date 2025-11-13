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
        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('prefix_name', 50)->nullable()->comment('คำนำหน้าชื่อ');
            $table->string('name', 100)->nullable()->comment('ชื่อ');
            $table->string('last_name', 100)->nullable()->comment('นามสกุล');

            // ข้อมูลสถาบันและวุฒิการศึกษา
            $table->string('institution', 255)->nullable()->comment('สถาบันการศึกษา');
            $table->string('degree', 150)->nullable()->comment('ชื่อวุฒิการศึกษา เช่น วศ.บ., บช.บ.');
            $table->string('education_level', 100)->nullable()->comment('ระดับการศึกษา เช่น ปริญญาตรี, ปวส.');

            // ข้อมูลสาขาวิชา
            $table->string('faculty', 150)->nullable()->comment('คณะ');
            $table->string('major', 150)->nullable()->comment('สาขาวิชา/เอก');
            $table->string('minor', 150)->nullable()->comment('วิชาโท')->nullable();

            // ข้อมูลวัน/ปีที่
            $table->date('date_of_admission')->nullable()->comment('วันที่เข้าศึกษา');
            $table->date('date_of_graduation')->nullable()->comment('วันที่สำเร็จการศึกษา');

            // เกรดเฉลี่ย
            $table->decimal('gpa', 4, 2)->nullable()->comment('เกรดเฉลี่ย (x.xx)'); // 4 หลัก, ทศนิยม 2 ตำแหน่ง (รองรับ 99.99 แต่ใช้จริงแค่ 4.00)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
};
