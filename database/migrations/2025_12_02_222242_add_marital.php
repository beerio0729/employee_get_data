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
        Schema::table('maritals', function (Blueprint $table) {
            $table->string('age')->nullable()->after('issue_date');
            $table->boolean('alive')->nullable()->after('age');
            $table->string('occupation')->nullable()->after('alive');
            $table->string('company')->nullable()->after('occupation');
            $table->integer('no_of_children')->nullable()->after('company');
            $table->integer('male')->nullable()->after('no_of_children');
            $table->integer('female')->nullable()->after('male');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maritals', function (Blueprint $table) {
            //
        });
    }
};
