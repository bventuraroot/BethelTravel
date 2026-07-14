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
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'nu_unico')) {
                $table->string('nu_unico')->nullable()->after('acuenta');
            }
            if (!Schema::hasColumn('sales', 'nu_doc')) {
                $table->string('nu_doc')->nullable()->after('nu_unico');
            }
            if (!Schema::hasColumn('sales', 'retencion_agente')) {
                $table->decimal('retencion_agente', 12, 4)->default(0)->nullable()->after('totalamount');
            }
            if (!Schema::hasColumn('sales', 'json')) {
                $table->longText('json')->nullable()->after('company_id');
            }
            if (!Schema::hasColumn('sales', 'doc_related')) {
                $table->text('doc_related')->nullable()->after('json');
            }
            if (!Schema::hasColumn('sales', 'id_contingencia')) {
                $table->string('id_contingencia')->nullable()->after('doc_related');
            }
            if (!Schema::hasColumn('sales', 'codigoGeneracion')) {
                $table->string('codigoGeneracion')->nullable()->after('id_contingencia');
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
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'nu_unico',
                'nu_doc',
                'retencion_agente',
                'json',
                'doc_related',
                'id_contingencia',
                'codigoGeneracion'
            ]);
        });
    }
};
