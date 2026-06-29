<?php

namespace App\Http\Controllers;

use App\Services\AffectedSampleService;
use Illuminate\Http\JsonResponse;

class AffectedSampleController extends Controller
{
    /**
     * Inject AffectedSampleService melalui constructor injection.
     */
    public function __construct(
        private readonly AffectedSampleService $affectedSampleService
    ) {}

    /**
     * Tampilkan semua insiden kondisi (indicator_color = 'red' atau 'yellow')
     * beserta jumlah sampel terdampak di setiap insiden.
     *
     * GET /api/affected-samples
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Ambil semua insiden + hitungan sampel terdampak dari service
            $incidents = $this->affectedSampleService->getAllIncidentsWithCount();

            return response()->json([
                'status'  => 'success',
                'message' => 'Daftar insiden kondisi berhasil diambil.',
                'total'   => $incidents->count(),
                'data'    => $incidents->map(fn ($incident) => [
                    'incident_id'            => $incident->id,
                    'storage_room'           => $incident->room?->room_name,
                    'storage_room_id'        => $incident->storage_room_id,
                    'temperature'            => $incident->temperature,
                    'humidity'               => $incident->humidity,
                    'indicator_color'        => $incident->indicator_color,
                    'occurred_at'            => $incident->created_at,
                    'affected_samples_count' => $incident->affected_samples_count,
                ]),
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal mengambil data insiden: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Tampilkan detail satu insiden beserta daftar lengkap sampel terdampak.
     *
     * GET /api/affected-samples/{conditionDataId}
     *
     * @param  int  $conditionDataId
     * @return JsonResponse
     */
    public function show(int $conditionDataId): JsonResponse
    {
        try {
            // Delegasikan logika identifikasi ke service layer
            $result = $this->affectedSampleService->getAffectedSamples($conditionDataId);

            $incident        = $result['incident'];
            $affectedSamples = $result['affected_samples'];

            return response()->json([
                'status'  => 'success',
                'message' => "Daftar sampel terdampak insiden ID {$conditionDataId} berhasil diambil.",

                // Detail insiden kondisi
                'incident' => [
                    'id'              => $incident->id,
                    'storage_room'    => $incident->room?->room_name,
                    'storage_room_id' => $incident->storage_room_id,
                    'temperature'     => $incident->temperature,
                    'humidity'        => $incident->humidity,
                    'indicator_color' => $incident->indicator_color,
                    'occurred_at'     => $incident->created_at,
                ],

                // Daftar sampel yang terdampak saat insiden berlangsung
                'affected_samples' => $affectedSamples->map(fn ($sample) => [
                    'id'              => $sample->id,
                    'sample_code'     => $sample->sample_code,
                    'sample_name'     => $sample->sample_name,
                    'storage_room'    => $sample->storageRoom?->room_name,
                    'stored_at'       => $sample->stored_at,
                    'withdrawn_at'    => $sample->withdrawn_at,
                    'status'          => $sample->status,
                ]),

                'total_affected' => $affectedSamples->count(),
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'status'  => 'error',
                'message' => "Data kondisi dengan ID {$conditionDataId} tidak ditemukan.",
            ], 404);

        } catch (\InvalidArgumentException $e) {
            // Terjadi ketika incident_color = 'green' (bukan insiden)
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan server: ' . $e->getMessage(),
            ], 500);
        }
    }
}
