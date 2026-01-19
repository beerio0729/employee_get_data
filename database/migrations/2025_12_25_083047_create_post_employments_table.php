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
        Schema::create('post_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_status_id')->nullable()->constrained('work_statuses')->onDelete('cascade');
            $table->foreignId('position_id')->nullable()->constrained('organization_structures')->nullOnDelete();
            $table->string('employee_code')->unique(); // รหัสพนักงาน ไม่ซ้ำ
            $table->foreignId('lowest_org_structure_id')->nullable()->constrained('organization_structures')->nullOnDelete();
            $table->foreignId('post_employment_grade_id')->nullable()->constrained('post_employment_grades')->nullOnDelete();
            $table->string('salary')->nullable();
            $table->date('hired_at'); // วันที่เริ่มงาน ต้องมี
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_employments');
    }
};
