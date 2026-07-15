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
        if (Schema::hasTable('dte_errors')) {
            try {
                \DB::statement("ALTER TABLE dte_errors MODIFY COLUMN tipo_error VARCHAR(150) NULL");
            } catch (\Exception $e) {}

            Schema::table('dte_errors', function (Blueprint $table) {
                // Agregar solo las columnas que faltan
                if (!Schema::hasColumn('dte_errors', 'intentos_realizados')) {
                    $table->integer('intentos_realizados')->default(0)->after('resolved_at');
                }
                if (!Schema::hasColumn('dte_errors', 'max_intentos')) {
                    $table->integer('max_intentos')->default(3)->after('intentos_realizados');
                }

                // Agregar índices para mejorar el rendimiento
                // Wrap in try-catch to avoid index creation errors if they already exist
                try {
                    $table->index(['tipo_error', 'resuelto']);
                } catch (\Exception $e) {}
                try {
                    $table->index(['dte_id', 'resuelto']);
                } catch (\Exception $e) {}
                try {
                    $table->index('resolved_by');
                } catch (\Exception $e) {}
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dte_errors', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex(['tipo_error', 'resuelto']);
            $table->dropIndex(['dte_id', 'resuelto']);
            $table->dropIndex(['resolved_by']);

            // Eliminar columnas agregadas
            $table->dropColumn([
                'intentos_realizados',
                'max_intentos'
            ]);
        });
    }
};
