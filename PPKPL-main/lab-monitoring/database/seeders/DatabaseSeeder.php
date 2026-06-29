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
        // 1. Buat 1 User agar ID 1 tersedia untuk form monitoring
        User::factory()->create([
            'name' => 'Petugas Demo',
            'email' => 'petugas@example.com',
        ]);

        User::factory()->create([
            'name' => 'Manajer Laboratorium',
            'email' => 'manajer@example.com',
            'role' => 'admin', // Ini yang dicari oleh AlertService Anda
        ]);

        // 2. Panggil seeder ruangan penyimpanan
        $this->call([
            StorageRoomSeeder::class,
        ]);

        $this->call([
            StorageRoomSeeder::class,
        ]);
    }
}