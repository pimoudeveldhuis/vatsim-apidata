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
        Schema::create('archive_prefiles', function (Blueprint $table) {
            $table->id();

            $table->integer('cid')->index();
            $table->string('name');
            $table->string('callsign');

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

            $table->datetime('last_update')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_prefiles');
    }
};
