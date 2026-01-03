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
        Schema::create('organization_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name_th');
            $table->string('name_en');
            $table->string('code')->nullable(); // ตัวย่อจากชื่อ en
            $table->unsignedSmallInteger('max_count')->nullable();
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('organization_structures')
                ->cascadeOnDelete();
            $table->foreignId('organization_level_id')
                ->nullable()
                ->constrained('organization_levels')
                ->nullOnDelete(); // 🔑 สำคัญมาก
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_structures');
    }
};
