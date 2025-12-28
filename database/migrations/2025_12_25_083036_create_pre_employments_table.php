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
        Schema::create('pre_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('status_id')->nullable()->constrained('work_status_defination_details')->nullOnDelete();
            $table->timestamp('applied_at');  // วันที่สมัคร (ต้องมี)
            $table->timestamp('interview_at')->nullable(); // วันสัมภาษณ์ (อาจยังไม่มี)
            $table->timestamp('result_at')->nullable();  // วันที่สรุปผล (อาจยังไม่มี)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_employments');
    }
};
