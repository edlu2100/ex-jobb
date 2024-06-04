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
        Schema::create('dns_checks', function (Blueprint $table) {
            $table->id();
            $table->string('URL');
            $table->boolean('NS_servers')->nullable();
            $table->boolean('DNS_records')->nullable();
            $table->string('Error_message', 1000)->nullable();
            $table->timestamps();
            $table->bigInteger('Websites_id')->unsigned();
            $table->foreign('Websites_id')
                  ->references('id')
                  ->on('websites')
                  ->onDelete('cascade'); // Add this line
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dns_checks');
    }
};
