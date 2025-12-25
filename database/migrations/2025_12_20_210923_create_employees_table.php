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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained('users')->onDelete('cascade');
            $table->string('employee_code')->unique(); // รหัสพนักงาน ไม่ซ้ำ
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->date('hired_at'); // วันที่เริ่มงาน ต้องมี
            $table->enum('status', ['active','resigned','terminated'])->default('active'); // สถานะเริ่มต้น
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
