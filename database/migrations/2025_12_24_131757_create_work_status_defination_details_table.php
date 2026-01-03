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
        Schema::create('work_status_defination_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_status_def_id')->constrained('work_status_definations')->onDelete('cascade');
            $table->string('name_th');
            $table->string('name_en');
            $table->unsignedInteger('max_emp_count')->nullable();
            $table->string('work_phase')->nullable(); // ใช้ enum หรือ string ก็ได้
            $table->string('code')->nullable();
            $table->enum('color', ['primary', 'success', 'warning', 'danger'])->default('primary');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_status_defination_details');
    }
};
