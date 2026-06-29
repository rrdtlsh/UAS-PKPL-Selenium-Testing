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
        Schema::create('condition_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_room_id')->constrained('storage_rooms')->onDelete('cascade');
            $table->foreignId('inputted_by')->constrained('users')->onDelete('cascade');
            $table->float('temperature');
            $table->float('humidity');
            $table->enum('indicator_color', ['green', 'yellow', 'red']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condition_data');
    }
};
