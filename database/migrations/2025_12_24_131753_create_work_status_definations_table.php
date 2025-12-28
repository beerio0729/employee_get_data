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
        Schema::create('work_status_definations', function (Blueprint $table) {
            $table->id();
            //$table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code')->nullable(); // สำหรับ internal logic
            $table->string('name_th')->nullable();
            $table->string('name_en')->nullable();
            $table->enum('main_work_status', ['pre_employment', 'post_employment']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_status_definations');
    }
};
