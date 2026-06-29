<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi — buat tabel test_samples.
     */
    public function up(): void
    {
        Schema::create('test_samples', function (Blueprint $table) {
            $table->id();

            // Kode unik sampel (misal: SP-2024-001)
            $table->string('sample_code')->unique();

            // Nama deskriptif sampel uji
            $table->string('sample_name');

            // Relasi ke ruang penyimpanan; cascade agar sampel ikut terhapus
            // jika ruang penyimpanan dihapus
            $table->foreignId('storage_room_id')
                  ->constrained('storage_rooms')
                  ->onDelete('cascade');

            // Waktu sampel mulai disimpan di ruangan
            $table->timestamp('stored_at');

            // Waktu sampel dikeluarkan dari ruangan (null = masih tersimpan)
            $table->timestamp('withdrawn_at')->nullable();

            // Status keberadaan sampel: 'active' = masih di ruangan
            $table->enum('status', ['active', 'withdrawn'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Rollback migrasi — hapus tabel test_samples.
     */
    public function down(): void
    {
        Schema::dropIfExists('test_samples');
    }
};
