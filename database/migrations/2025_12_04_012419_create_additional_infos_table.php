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
        Schema::create('additional_infos', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('emergency_name')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('emergency_tel')->nullable();
            $table->text('emergency_address')->nullable();
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('subdistrict_id')->nullable();
            $table->string('zipcode')->nullable();
            
            $table->boolean('worked_company_before')->nullable();
            $table->text('worked_company_detail')->nullable();
            $table->string('worked_company_supervisor')->nullable();

            $table->boolean('know_someone')->nullable();
            $table->string('know_someone_name')->nullable();
            $table->string('know_someone_relation')->nullable();

            $table->string('how_to_know_job')->nullable();

            $table->boolean('medical_condition')->nullable();
            $table->text('medical_condition_detail')->nullable();

            $table->boolean('has_sso')->nullable();
            $table->string('sso_hospital')->nullable();

            $table->text('additional_info')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('additional_infos');
    }
};
