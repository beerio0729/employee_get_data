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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignID('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('name'); // ชื่อตำแหน่ง
            $table->unsignedInteger('sort_order')->default(0); // ลำดับการแสดงผลภายในแผนก
            $table->boolean('is_active')->default(true); // ใช้งาน / ไม่ใช้งาน
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
