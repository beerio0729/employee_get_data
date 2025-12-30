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
        Schema::create('work_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('work_status_def_detail_id')->nullable()->constrained('work_status_defination_details')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_statuses');
    }
};
