<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: add_created_by_to_incident_tickets_table
 *
 * Menambahkan kolom created_by ke tabel incident_tickets untuk menyimpan
 * referensi user yang memicu pembuatan tiket (diambil dari ConditionData.inputted_by).
 *
 * Nullable agar data incident_tickets yang sudah ada sebelumnya tidak rusak.
 * Foreign key ke tabel users; jika user dihapus, kolom diset NULL (nullOnDelete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incident_tickets', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('condition_data_id')
                ->constrained('users')
                ->nullOnDelete()
                ->comment('User yang menginput data kondisi pemicu tiket ini (dari ConditionData.inputted_by)');
        });
    }

    public function down(): void
    {
        Schema::table('incident_tickets', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
