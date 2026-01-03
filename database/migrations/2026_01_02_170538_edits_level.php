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
        Schema::table('organization_structures', function (Blueprint $table) {
            $table->dropForeign('organization_structures_organization_level_id_foreign');
        });
        Schema::table('organization_structures', function (Blueprint $table) {
            $table->dropColumn('organization_level_id');
        });
        Schema::table('organization_structures', function (Blueprint $table) {
            $table->foreignId('organization_level_id')
                ->nullable()
                ->after('parent_id');
        });

        Schema::table('organization_structures', function (Blueprint $table) {
            $table->foreign('organization_level_id')
                ->references('id')
                ->on('organization_levels')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
