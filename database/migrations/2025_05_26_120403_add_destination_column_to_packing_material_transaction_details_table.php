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
        Schema::table('packing_material_transaction_details', function (Blueprint $table) {
            $table->string('destination')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packing_material_transaction_details', function (Blueprint $table) {
            $table->dropColumn('destination');
        });
    }
};
