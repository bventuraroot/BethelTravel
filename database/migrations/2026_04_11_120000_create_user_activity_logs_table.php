<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_activity_logs')) {
            Schema::create('user_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('method', 12);
                $table->string('route_name')->nullable();
                $table->string('path', 512);
                $table->string('action_label', 512);
                $table->unsignedSmallInteger('status_code')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
