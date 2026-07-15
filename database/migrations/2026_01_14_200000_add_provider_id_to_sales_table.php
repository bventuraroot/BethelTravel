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
            if (!Schema::hasColumn('sales', 'provider_id')) {
                $table->foreignId('provider_id')
                      ->nullable()
                      ->after('client_id')
                      ->constrained('providers')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (Schema::hasColumn('sales', 'provider_id')) {
                $table->dropForeign(['provider_id']);
                $table->dropColumn('provider_id');
            }
        });
    }
};
