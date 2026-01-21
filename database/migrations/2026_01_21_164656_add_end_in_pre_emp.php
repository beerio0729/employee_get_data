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
        Schema::table('pre_employments', function (Blueprint $table) {
            // ลบคอลัมน์เดิม
            $table->dropColumn('interview_at');

            // เพิ่มคอลัมน์ใหม่
            $table->timestamp('start_interview_at')
                ->nullable()
                ->after('applied_at');

            $table->timestamp('end_interview_at')
                ->nullable()
                ->after('start_interview_at');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pre_employments', function (Blueprint $table) {
            //
        });
    }
};
