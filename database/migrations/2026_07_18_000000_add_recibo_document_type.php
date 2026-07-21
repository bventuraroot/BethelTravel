<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insertar tipo de documento RECIBO DE INGRESO
        DB::table('typedocuments')->updateOrInsert(
            ['type' => 'REC'],
            [
                'id' => 12,
                'company_id' => '1',
                'description' => 'RECIBO DE INGRESO',
                'codemh' => '00',
                'versionjson' => '1',
                'versionjsoncontingencia' => '0',
                'contingencia' => '0',
                'ambiente' => '0',
                'invalidation' => '0',
                'periodinvalidation' => '0',
                'versionjsoncontingenciainvalidation' => '0',
                'created_at' => now(),
                'updated_at' => now()
            ]
        );

        // Obtener el ID del primer usuario para 'hechopor'
        $userId = DB::table('users')->value('id') ?? 1;

        // Obtener todas las empresas existentes para crearles un correlativo para RECIBO
        $companies = DB::table('companies')->select('id')->get();

        foreach ($companies as $company) {
            DB::table('docs')->updateOrInsert(
                [
                    'id_tipo_doc' => 'REC',
                    'id_empresa' => $company->id
                ],
                [
                    'serie' => 'REC-01',
                    'inicial' => 1,
                    'final' => 999999,
                    'actual' => 1,
                    'estado' => 1, // Activo
                    'hechopor' => $userId,
                    'fechacreacion' => now(),
                    'resolucion' => 'RECIBO INTERNO',
                    'clase_documento' => '1',
                    'tipo_documento' => '01',
                    'tipogeneracion' => 1,
                    'ambiente' => '00',
                    'claseDocumento' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('docs')->where('id_tipo_doc', 'REC')->delete();
        DB::table('typedocuments')->where('type', 'REC')->delete();
    }
};
