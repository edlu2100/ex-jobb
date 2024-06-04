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
        Schema::create('ssl_checks', function (Blueprint $table) {
            $table->id();
            $table->string('URL', 300);
            $table->boolean('Valid');
            $table->date('Expiration_date')->nullable();
            $table->timestamps();
            $table->bigInteger('Websites_id')->unsigned();
            $table->foreign('Websites_id')->references('id')->on('websites');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssl_checks');
    }
};
