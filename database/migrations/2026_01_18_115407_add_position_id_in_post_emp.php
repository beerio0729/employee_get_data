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
            $table->dropForeign('post_employments_position_id_foreign');
            $table->dropColumn('position_id');
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
