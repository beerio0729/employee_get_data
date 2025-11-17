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
        Schema::create('doc_emps', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('file_name')->nullable();
            $table->string('file_name_th')->nullable();
            $table->string('path')->nullable();
            $table->boolean('confirm')->default(false);
            $table->boolean('check')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doc_emps');
    }
};
