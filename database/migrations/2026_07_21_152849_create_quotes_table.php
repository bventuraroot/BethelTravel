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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null');
            
            // For potential clients not yet in the DB
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('client_phone')->nullable();
            
            // Quote basic info
            $table->string('title'); // e.g. "MEDELLIN"
            $table->string('subtitle')->nullable(); // e.g. "DEL 14-19 DE AGOSTO 2026"
            
            // JSON fields for dynamic structure
            $table->json('banner_images')->nullable(); // Up to 3 landscape image paths
            $table->json('includes')->nullable(); // Bullet points for inclusions
            $table->json('hotels_grid')->nullable(); // Category name, columns (Sencilla, Doble, etc), rows, footer
            $table->json('flights')->nullable(); // Array of flight segments
            $table->json('notes')->nullable(); // Important notes
            
            // Status and owner
            $table->string('status')->default('draft'); // draft, sent, approved, declined
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            
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
        Schema::dropIfExists('quotes');
    }
};
