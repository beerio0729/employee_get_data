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
        Schema::create('id_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('id_card_number')->nullable();
            $table->string('prefix_name_th')->nullable();
            $table->string('name_th')->nullable();
            $table->string('last_name_th')->nullable();
            $table->string('prefix_name_en')->nullable();
            $table->string('name_en')->nullable();
            $table->string('last_name_en')->nullable();
            $table->string('religion')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->text('address')->nullable();
            $table->integer('province_id')->nullable();
            $table->integer('district_id')->nullable();
            $table->integer('subdistrict_id')->nullable();
            $table->integer('zipcode')->nullable();
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
        Schema::dropIfExists('id_cards');
    }
};
