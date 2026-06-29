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
        Schema::create('storage_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_name');
            $table->float('temp_limit')->default(25.0);
            $table->float('humidity_limit')->default(60.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_rooms');
    }
};
