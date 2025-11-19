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
        Schema::create('another_docs', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('doc_type');
            $table->text('data'); // ถ้าเก็บข้อมูลหลายค่าแบบ array หรือ object
            $table->string('file_path');
            $table->date('date_of_issue')->nullable();
            $table->date('date_of_expiry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('another_docs');
    }
};
