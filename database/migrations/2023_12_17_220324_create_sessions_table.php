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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('snapshot_id')->index();
            $table->unsignedBigInteger('pilot_id')->index();
            $table->unsignedBigInteger('server_id')->index();
            $table->unsignedBigInteger('airline_id')->nullable()->index();

            $table->string('callsign')->index();
            $table->string('transponder')->index();

            $table->integer('flightplan_revision_id')->index()->nullable();

            $table->datetime('login_time')->index();
            $table->datetime('last_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
