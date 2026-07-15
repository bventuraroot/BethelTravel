<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('config', function (Blueprint $table) {
            // Agregar campos para control de emisión DTE
            if (!Schema::hasColumn('config', 'dte_emission_enabled')) {
                $table->boolean('dte_emission_enabled')->default(true)->after('nameCountry');
            }
            if (!Schema::hasColumn('config', 'dte_emission_notes')) {
                $table->text('dte_emission_notes')->nullable()->after('dte_emission_enabled');
            }
        });

        try {
            Schema::table('config', function (Blueprint $table) {
                // Agregar índice para mejorar consultas
                $table->index(['company_id', 'dte_emission_enabled']);
            });
        } catch (\Exception $e) {}
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        try {
            Schema::table('config', function (Blueprint $table) {
                // Eliminar índice primero
                $table->dropIndex(['company_id', 'dte_emission_enabled']);
            });
        } catch (\Exception $e) {}

        Schema::table('config', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('config', 'dte_emission_enabled')) {
                $columns[] = 'dte_emission_enabled';
            }
            if (Schema::hasColumn('config', 'dte_emission_notes')) {
                $columns[] = 'dte_emission_notes';
            }
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
