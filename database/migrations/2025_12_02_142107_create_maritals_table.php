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
        Schema::create('maritals', function (Blueprint $table) {
            $table->id();
            $table->foreignID('user_id')->constrained('users')->onDelete('cascade');
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('man')->nullable();
            $table->string('woman')->nullable();
            $table->date('issue_date')->nullable();
            $table->string('age')->nullable();
            $table->boolean('alive')->nullable();
            $table->string('occupation')->nullable();
            $table->string('company')->nullable();
            $table->integer('male')->nullable();
            $table->integer('female')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maritals');
    }
};
