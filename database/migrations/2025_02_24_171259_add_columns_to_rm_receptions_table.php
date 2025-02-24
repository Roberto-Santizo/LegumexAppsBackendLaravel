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
        Schema::table('rm_receptions', function (Blueprint $table) {
            $table->foreignId('finca_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rm_receptions', function (Blueprint $table) {
            $table->dropForeign('rm_receptions_finca_id_foreign');
            $table->dropColumn('finca_id');
        });
    }
};
