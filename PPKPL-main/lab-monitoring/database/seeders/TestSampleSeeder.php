<?php

namespace Database\Seeders;

use App\Models\TestSample;
use App\Models\StorageRoom;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TestSampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada ruangan penyimpanan sebelum insert sampel
        $storageRooms = StorageRoom::all();
        
        if ($storageRooms->count() < 2) {
            $this->command->warn('Harap buat minimal 2 storage rooms sebelum menjalankan seeder ini.');
            // Buat ruangan dummy jika tidak ada
            $room1 = StorageRoom::firstOrCreate(['room_name' => 'Ruang Kulkas 1'], ['temp_limit' => 5.0, 'humidity_limit' => 60.0]);
            $room2 = StorageRoom::firstOrCreate(['room_name' => 'Ruang Kering 1'], ['temp_limit' => 25.0, 'humidity_limit' => 40.0]);
            $storageRooms = collect([$room1, $room2]);
        }

        // Variasi waktu dalam 30 hari terakhir
        $now = Carbon::now();

        $samples = [
            [
                'sample_code'     => 'SPL-A001',
                'sample_name'     => 'Paracetamol 500mg Batch A',
                'storage_room_id' => $storageRooms[0]->id,
                'stored_at'       => $now->copy()->subDays(25),
                'withdrawn_at'    => null,
                'status'          => 'active',
            ],
            [
                'sample_code'     => 'SPL-A002',
                'sample_name'     => 'Ibuprofen 400mg Batch B',
                'storage_room_id' => $storageRooms[0]->id,
                'stored_at'       => $now->copy()->subDays(15),
                'withdrawn_at'    => null,
                'status'          => 'active',
            ],
            [
                'sample_code'     => 'SPL-B001',
                'sample_name'     => 'Amoxicillin Syrup',
                'storage_room_id' => $storageRooms[1]->id,
                'stored_at'       => $now->copy()->subDays(10),
                'withdrawn_at'    => null,
                'status'          => 'active',
            ],
            [
                'sample_code'     => 'SPL-B002',
                'sample_name'     => 'Vitamin C 1000mg',
                'storage_room_id' => $storageRooms[1]->id,
                'stored_at'       => $now->copy()->subDays(5),
                'withdrawn_at'    => null,
                'status'          => 'active',
            ],
            [
                'sample_code'     => 'SPL-B003',
                'sample_name'     => 'Antasida Doen Tablet',
                'storage_room_id' => $storageRooms[0]->id, // Bisa ruangan 0 atau 1
                'stored_at'       => $now->copy()->subDays(2),
                'withdrawn_at'    => null,
                'status'          => 'active',
            ],
        ];

        foreach ($samples as $sampleData) {
            TestSample::create($sampleData);
        }

        $this->command->info('5 Contoh Data TestSample berhasil dibuat.');
    }
}
