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
        Schema::table('salesdetails', function (Blueprint $table) {
            $table->date('fecha_viaje')->nullable()->after('canal');
            $table->string('precheckin_status', 30)->default('pendiente')->after('fecha_viaje');
            $table->text('precheckin_notes')->nullable()->after('precheckin_status');
            $table->boolean('precheckin_email_sent')->default(0)->after('precheckin_notes');
            $table->timestamp('precheckin_email_sent_at')->nullable()->after('precheckin_email_sent');
            $table->timestamp('precheckin_completed_at')->nullable()->after('precheckin_email_sent_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salesdetails', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_viaje',
                'precheckin_status',
                'precheckin_notes',
                'precheckin_email_sent',
                'precheckin_email_sent_at',
                'precheckin_completed_at'
            ]);
        });
    }
};
