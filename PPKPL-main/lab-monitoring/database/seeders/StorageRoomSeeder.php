<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StorageRoom;

class StorageRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StorageRoom::insert([
            [
                'room_name'      => 'Ruang Vaksin',
                'temp_limit'     => 8.0,
                'humidity_limit' => 50.0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'room_name'      => 'Ruang Kimia',
                'temp_limit'     => 25.0,
                'humidity_limit' => 60.0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ],
            [
                'room_name'      => 'Ruang Umum',
                'temp_limit'     => 30.0,
                'humidity_limit' => 70.0,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]
        ]);
    }
}
