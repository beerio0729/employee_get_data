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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();

            // ข้อมูลบริษัท
            $table->string('name')->nullable();
            $table->string('tax_id', 13)->nullable();

            // ที่อยู่ (ของเดิม)
            $table->text('address')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('subdistrict_id')->nullable();
            $table->string('zipcode', 5)->nullable();

            // ผู้มีอำนาจลงนาม
            $table->string('authorized_person_name')->nullable();
            $table->string('authorized_person_position')->nullable();

            // ติดต่อ
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_profiles');
    }
};
