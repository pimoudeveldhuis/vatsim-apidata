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
        Schema::create('archive_sessions', function (Blueprint $table) {
            $table->id();

            $table->dateTime('start')->index();
            $table->dateTime('end')->index();

            $table->unsignedBigInteger('pilot_id')->index();
            $table->unsignedBigInteger('server_id')->index();
            $table->unsignedBigInteger('airline_id')->nullable()->index();

            $table->string('callsign')->index();

            $table->json('transponder_codes');
            $table->json('flightplans');

            $table->json('logs');

            $table->boolean('finished')->default(false)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_sessions');
    }
};
