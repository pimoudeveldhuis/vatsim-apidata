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
        Schema::create('flightplans', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('session_id')->index();
            $table->unsignedBigInteger('snapshot_id')->index();

            $table->integer('revision_id')->index();

            $table->string('flight_rules')->nullable();
            $table->string('aircraft')->nullable();
            $table->string('aircraft_faa')->nullable();
            $table->string('aircraft_short')->nullable();
            $table->string('departure')->nullable();
            $table->string('arrival')->nullable();
            $table->string('alternate')->nullable();
            $table->string('cruise_tas')->nullable();
            $table->string('altitude')->nullable();
            $table->string('deptime')->nullable();
            $table->string('enroute_time')->nullable();
            $table->string('fuel_time')->nullable();
            $table->text('remarks')->nullable();
            $table->text('route')->nullable();
            $table->string('assigned_transponder')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flightplans');
    }
};
