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
        Schema::create('archive_controllers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('server_id')->index();

            $table->integer('cid')->index();
            $table->string('name');
            $table->string('callsign')->nullable();
            $table->string('frequency');
            $table->integer('facility');
            $table->integer('rating');
            $table->integer('visual_range');
            $table->json('text_atis')->nullable();

            $table->datetime('login_time')->index();
            $table->datetime('last_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_controllers');
    }
};
