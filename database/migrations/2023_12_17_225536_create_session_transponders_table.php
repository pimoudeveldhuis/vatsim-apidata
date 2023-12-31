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
        Schema::create('session_transponders', function (Blueprint $table) {
            $table->unsignedBigInteger('session_id')->index();
            $table->unsignedBigInteger('snapshot_id')->index();

            $table->string('transponder')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_transponders');
    }
};
