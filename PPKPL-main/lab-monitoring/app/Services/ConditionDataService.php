<?php

namespace App\Services;

use App\Models\ConditionData;
use App\Models\StorageRoom;

/**
 * ConditionDataService
 *
 * Bertanggung jawab atas kalkulasi indikator warna dan
 * penyimpanan data kondisi ruang penyimpanan.
 *
 * Logika Indikator (berdasarkan % deviasi dari limit):
 *   - Green  : Nilai ≤ batas (0% deviasi ke atas)
 *   - Yellow : Nilai melewati batas hingga +10% (Level 1 - Peringatan)
 *   - Red    : Nilai melewati batas lebih dari +10% (Level 2 - Kritis)
 */
class ConditionDataService
{
    /**
     * Menghitung warna indikator berdasarkan deviasi relatif
     * terhadap batas toleransi ruang penyimpanan.
     *
     * @param  float       $temp  Suhu aktual (°C)
     * @param  float       $hum   Kelembaban aktual (%)
     * @param  StorageRoom $room  Objek ruang dengan temp_limit & humidity_limit
     * @return string             'green' | 'yellow' | 'red'
     */
    public function calculateIndicatorColor(float $temp, float $hum, StorageRoom $room): string
    {
        $tempPercentOver = ($temp - $room->temp_limit) / $room->temp_limit * 100;
        $humPercentOver  = ($hum  - $room->humidity_limit) / $room->humidity_limit * 100;

        if ($tempPercentOver > 10 || $humPercentOver > 10) {
            // Melebihi lebih dari 10% di atas batas → Kritis
            return 'red';
        }

        if ($tempPercentOver > 0 || $humPercentOver > 0) {
            // Melewati batas namun belum lebih dari 10% → Peringatan
            return 'yellow';
        }

        // Semua nilai dalam batas toleransi → Normal
        return 'green';
    }

    /**
     * Membuat dan menyimpan entri ConditionData baru ke database.
     *
     * Metode ini memisahkan tanggung jawab penyimpanan dari Controller,
     * sehingga Controller tetap tipis (thin controller principle).
     *
     * @param  array<string, mixed> $validated Data tervalidasi dari Form Request
     * @param  StorageRoom          $room      Objek ruang penyimpanan
     * @return ConditionData                   Model yang baru disimpan
     */
    public function store(array $validated, StorageRoom $room): ConditionData
    {
        $indicatorColor = $this->calculateIndicatorColor(
            (float) $validated['temperature'],
            (float) $validated['humidity'],
            $room,
        );

        return ConditionData::create([
            'storage_room_id' => $validated['storage_room_id'],
            'inputted_by'     => $validated['inputted_by'],
            'temperature'     => $validated['temperature'],
            'humidity'        => $validated['humidity'],
            'indicator_color' => $indicatorColor,
        ]);
    }
}
