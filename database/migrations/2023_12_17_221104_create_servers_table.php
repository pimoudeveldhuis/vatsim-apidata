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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('first_seen_snapshot_id')->index();

            $table->string('ident')->index();
            $table->string('hostname_or_ip');
            $table->string('location');
            $table->string('name');

            $table->integer('clients_connection_allowed');
            $table->boolean('client_connections_allowed');
            $table->boolean('is_sweatbox');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
