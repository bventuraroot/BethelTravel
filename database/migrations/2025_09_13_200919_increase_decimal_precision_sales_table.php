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
        Schema::table('sales', function (Blueprint $table) {
            // Aumentar precisión de decimales de (5,2) a (18,8) para mayor precisión en cálculos
            $table->decimal('totalamount', 18, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            // Revertir a la precisión original
            $table->decimal('totalamount', 5, 2)->nullable()->change();
        });
    }
};
