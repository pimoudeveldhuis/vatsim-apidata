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
        Schema::create('archive_atis', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('server_id')->index();

            $table->string('atis_code')->nullable();
            $table->json('atis_text')->nullable();

            $table->string('cid');
            $table->string('name');
            $table->string('callsign');
            $table->integer('frequency');
            $table->integer('facility');
            $table->integer('rating');
            $table->integer('visual_range');

            $table->datetime('login_time')->index();
            $table->datetime('last_update');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_atis');
    }
};
