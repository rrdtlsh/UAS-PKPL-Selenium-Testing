<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: create_incident_tickets_table
 *
 * Tabel ini mencatat setiap insiden deviasi Level 2 (Kritis)
 * yang terdeteksi secara otomatis saat data kondisi diinput.
 *
 * Standar ALCOA+: setiap tiket memiliki audit trail lengkap
 * melalui timestamps (created_at = waktu insiden terdeteksi).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('incident_tickets', function (Blueprint $table) {
            $table->id();

            // Foreign key ke ruang penyimpanan yang mengalami deviasi
            $table->foreignId('storage_room_id')
                ->constrained('storage_rooms')
                ->onDelete('cascade')
                ->comment('Ruang penyimpanan yang mengalami deviasi');

            // Foreign key ke data kondisi pemicu insiden
            $table->foreignId('condition_data_id')
                ->constrained('condition_data')
                ->onDelete('cascade')
                ->comment('Data kondisi yang memicu pembuatan tiket ini');

            // Level deviasi: '1' = Peringatan, '2' = Kritis
            $table->enum('deviation_level', ['1', '2'])
                ->default('2')
                ->comment('Level 1: Peringatan (kuning), Level 2: Kritis (merah)');

            // Status penanganan tiket
            $table->enum('status', ['open', 'dalam_penanganan', 'closed'])
                ->default('open')
                ->comment('open: belum ditangani, closed: sudah diselesaikan');

            // ALCOA+ - Attributable & Timely: timestamps otomatis mencatat kapan insiden terdeteksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incident_tickets');
    }
};
