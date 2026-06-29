<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ──────────────────────────────────────────────────────────────
        // User Selenium / Testing
        // Gunakan firstOrCreate agar aman dijalankan ulang (idempotent)
        // ──────────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@lab.com'],
            [
                'name'     => 'Administrator Lab',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role'     => 'admin',
            ]
        );

        User::firstOrCreate(
            ['email' => 'petugas@lab.com'],
            [
                'name'     => 'Petugas Lab',
                'password' => \Illuminate\Support\Facades\Hash::make('password123'),
                'role'     => 'petugas',
            ]
        );

        // ──────────────────────────────────────────────────────────────
        // User lama (dipertahankan agar data existing tidak rusak)
        // ──────────────────────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'petugas@example.com'],
            [
                'name'     => 'Petugas Demo',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role'     => 'petugas',
            ]
        );

        User::firstOrCreate(
            ['email' => 'manajer@example.com'],
            [
                'name'     => 'Manajer Laboratorium',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role'     => 'admin',
            ]
        );

        // ──────────────────────────────────────────────────────────────
        // Seeder ruangan penyimpanan (dipanggil SEKALI — bukan dua kali)
        // ──────────────────────────────────────────────────────────────
        $this->call([
            StorageRoomSeeder::class,
        ]);
    }
}