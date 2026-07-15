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
        Schema::create('precheckin_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('cascade');
            $table->integer('dias_antes')->default(2);
            $table->boolean('enviar_cliente')->default(1);
            $table->string('email_agencia')->nullable();
            $table->string('asunto')->default('Prechequeo disponible para tu vuelo - Reserva {reserva}');
            $table->text('cuerpo')->nullable();
            $table->boolean('active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('precheckin_configs');
    }
};
