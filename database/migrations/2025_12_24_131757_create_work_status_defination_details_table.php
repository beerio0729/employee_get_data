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
            $table->string('code')->nullable();
            $table->string('name_th');
            $table->string('name_en');
            $table->string('work_phase')->nullable(); // ใช้ enum หรือ string ก็ได้
            $table->enum('color', ['success', 'warning','danger'])->default('warning');
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
