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
        Schema::create('packing_material_transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pm_transaction_id')->constrained('packing_material_transactions');
            $table->foreignId('packing_material_id')->constrained('packing_materials');
            $table->float('quantity');
            $table->string('lote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_material_transaction_details');
    }
};
