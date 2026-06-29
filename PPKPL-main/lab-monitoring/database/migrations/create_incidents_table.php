<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storage_room_id')->constrained('storage_rooms')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('assigned_qa_id')->constrained('users')->onDelete('restrict');
            $table->string('title', 200);
            $table->text('description');
            $table->string('deviation_level', 10); // green | yellow | red
            $table->string('status', 20)->default('open'); // open | in_progress | closed
            $table->timestamp('deviation_start');
            $table->timestamp('deviation_end')->nullable();
            $table->text('closing_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
