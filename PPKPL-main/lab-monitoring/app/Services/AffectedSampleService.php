<?php

namespace App\Services;

use App\Models\ConditionData;
use App\Models\TestSample;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AffectedSampleService
{
    /**
     * Identifikasi sampel uji yang terdampak oleh suatu insiden kondisi.
     *
     * Logika filter:
     *  - Insiden hanya berlaku jika indicator_color = 'red' atau 'yellow'
     *  - Sampel dianggap terdampak apabila:
     *      1. Berada di ruang penyimpanan yang SAMA dengan insiden
     *      2. stored_at <= created_at insiden  (sudah ada di ruangan saat insiden)
     *      3. withdrawn_at IS NULL             (belum ditarik), ATAU
     *         withdrawn_at >= created_at insiden (ditarik setelah insiden terjadi)
     *
     * @param  int  $conditionDataId  ID dari record condition_data
     * @return Collection             Koleksi hasil dengan detail insiden & sampel
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \InvalidArgumentException  Jika indicator_color = 'green' (bukan insiden)
     */
    public function getAffectedSamples(int $conditionDataId): Collection
    {
        // 1. Ambil data insiden beserta relasi ruangan
        $incident = ConditionData::with('room')
            ->findOrFail($conditionDataId);

        // 2. Pastikan data kondisi merupakan insiden (bukan hijau / normal)
        if ($incident->indicator_color === 'green') {
            throw new \InvalidArgumentException(
                "Condition data ID {$conditionDataId} berstatus 'green' dan bukan merupakan insiden."
            );
        }

        // 3. Waktu kejadian insiden (diambil dari created_at record kondisi)
        $incidentTime = $incident->created_at;

        // 4. Cari semua sampel di ruang penyimpanan yang sama
        //    kemudian terapkan filter waktu terdampak
        $affectedSamples = TestSample::with('storageRoom')
            ->where('storage_room_id', $incident->storage_room_id)
            // Sampel harus sudah ada di ruangan saat insiden terjadi
            ->where('stored_at', '<=', $incidentTime)
            // Sampel masih di ruangan (null) ATAU ditarik setelah insiden
            ->where(function ($query) use ($incidentTime) {
                $query->whereNull('withdrawn_at')
                      ->orWhere('withdrawn_at', '>=', $incidentTime);
            })
            ->get();

        // 5. Kembalikan koleksi yang membungkus detail insiden dan sampel terdampak
        return collect([
            'incident'         => $incident,
            'affected_samples' => $affectedSamples,
        ]);
    }

    /**
     * Ambil semua insiden (indicator_color != 'green') beserta
     * jumlah sampel terdampak di masing-masing insiden.
     *
     * @return Collection
     */
    public function getAllIncidentsWithCount(): Collection
    {
        // Ambil semua insiden dari tabel condition_data
        $incidents = ConditionData::with('room')
            ->whereIn('indicator_color', ['red', 'yellow'])
            ->latest()
            ->get();

        // Untuk setiap insiden, hitung jumlah sampel terdampak secara efisien
        return $incidents->map(function (ConditionData $incident) {
            $incidentTime = $incident->created_at;

            // Hitung sampel terdampak menggunakan query agregat
            $count = TestSample::where('storage_room_id', $incident->storage_room_id)
                ->where('stored_at', '<=', $incidentTime)
                ->where(function ($query) use ($incidentTime) {
                    $query->whereNull('withdrawn_at')
                          ->orWhere('withdrawn_at', '>=', $incidentTime);
                })
                ->count();

            // Tambahkan atribut virtual ke objek insiden
            $incident->affected_samples_count = $count;

            return $incident;
        });
    }
}
