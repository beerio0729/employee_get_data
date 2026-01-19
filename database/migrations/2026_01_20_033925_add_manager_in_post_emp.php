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
        Schema::table('post_employments', function (Blueprint $table) {

            //$table->dropForeign('post_employments_manager_foreign');

            $table->unsignedBigInteger('manager_id')
                ->nullable()
                ->after('work_status_id');
                
            $table->foreign('manager_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_employments', function (Blueprint $table) {
            //
        });
    }
};
