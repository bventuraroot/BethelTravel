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
        $created = false;
        if (!Schema::hasTable('contingencias')) {
            Schema::create('contingencias', function (Blueprint $table) {
                $table->id();
                $table->string('codInterno')->unique()->nullable();
                $table->unsignedBigInteger('idEmpresa');
                $table->string('versionJson')->nullable();
                $table->string('ambiente')->nullable();
                $table->string('codEstado')->default('01');
                $table->string('estado')->default('En Cola');
                $table->string('tipoContingencia')->nullable();
                $table->text('motivoContingencia')->nullable();
                $table->string('nombreResponsable')->nullable();
                $table->string('tipoDocResponsable')->nullable();
                $table->string('nuDocResponsable')->nullable();
                $table->datetime('fechaCreacion')->nullable();
                $table->date('fInicio')->nullable();
                $table->date('fFin')->nullable();
                $table->time('horaCreacion')->nullable();
                $table->time('hInicio')->nullable();
                $table->time('hFin')->nullable();
                $table->string('codigoGeneracion')->nullable();
                $table->string('selloRecibido')->nullable();
                $table->datetime('fhRecibido')->nullable();
                $table->string('codEstadoHacienda')->nullable();
                $table->string('estadoHacienda')->nullable();
                $table->string('codigoMsg')->nullable();
                $table->string('clasificaMsg')->nullable();
                $table->text('descripcionMsg')->nullable();
                $table->text('observacionesMsg')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();

                // Índices
                $table->index(['idEmpresa', 'codEstado']);
                $table->index(['fInicio', 'fFin']);
                $table->index('codigoGeneracion');

                // Claves foráneas
                $table->foreign('idEmpresa')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            });
            $created = true;
        }

        Schema::table('contingencias', function (Blueprint $table) use ($created) {
            // Agregar columnas para compatibilidad con el dashboard DTE
            if (!Schema::hasColumn('contingencias', 'activa')) {
                $table->boolean('activa')->default(true)->after('codEstado');
            }
            if (!Schema::hasColumn('contingencias', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('fInicio');
            }
            if (!Schema::hasColumn('contingencias', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fFin');
            }
            if (!Schema::hasColumn('contingencias', 'company_id')) {
                $table->unsignedBigInteger('company_id')->nullable()->after('idEmpresa');
            }
            if (!Schema::hasColumn('contingencias', 'nombre')) {
                $table->string('nombre')->nullable()->after('codInterno');
            }
            if (!Schema::hasColumn('contingencias', 'documentos_afectados')) {
                $table->integer('documentos_afectados')->default(0)->after('observacionesMsg');
            }

            // Agregar índices si la tabla fue creada recién o si no existen
            if ($created) {
                $table->index(['activa', 'fecha_fin']);
                $table->index('company_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('contingencias', function (Blueprint $table) {
            // Eliminar índices primero
            $table->dropIndex(['activa', 'fecha_fin']);
            $table->dropIndex(['company_id']);

            // Eliminar columnas agregadas
            $table->dropColumn([
                'activa',
                'fecha_inicio',
                'fecha_fin',
                'company_id',
                'nombre',
                'documentos_afectados'
            ]);
        });
    }
};
