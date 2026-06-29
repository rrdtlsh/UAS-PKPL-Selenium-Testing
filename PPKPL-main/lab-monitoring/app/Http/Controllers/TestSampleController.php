<?php

namespace App\Http\Controllers;

use App\Models\TestSample;
use App\Http\Requests\StoreTestSampleRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestSampleController extends Controller
{
    /**
     * Tampilkan semua sampel. Bisa di-filter melalui query param `?status=active`.
     *
     * GET /api/test-samples
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = TestSample::with('storageRoom');

            // Jika ada query parameter status=active, gunakan scopeActive()
            if ($request->query('status') === 'active') {
                $query->active();
            }

            $samples = $query->latest('stored_at')->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Daftar sampel berhasil diambil.',
                'data'    => $samples,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat mengambil data sampel.',
            ], 500);
        }
    }

    /**
     * Tambah sampel baru. Validasi ditangani oleh StoreTestSampleRequest.
     *
     * POST /api/test-samples
     */
    public function store(StoreTestSampleRequest $request): JsonResponse
    {
        try {
            // Data yang sudah divalidasi
            $validatedData = $request->validated();
            
            // Secara default sampel baru memiliki status 'active' dan 'withdrawn_at' = null
            $validatedData['status'] = 'active';
            $validatedData['withdrawn_at'] = null;

            $sample = TestSample::create($validatedData);

            return response()->json([
                'status'  => 'success',
                'message' => 'Sampel baru berhasil ditambahkan.',
                'data'    => $sample,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menambahkan sampel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update status sampel, misalnya untuk menarik sampel dari ruangan.
     *
     * PUT /api/test-samples/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $sample = TestSample::findOrFail($id);

            // Validasi input update sederhana
            $validated = $request->validate([
                'status'       => 'required|in:active,withdrawn',
                'withdrawn_at' => 'nullable|date|after_or_equal:stored_at',
            ], [
                'status.required' => 'Status rilis harus disertakan.',
                'status.in' => 'Status hanya boleh berisi active atau withdrawn.',
                'withdrawn_at.after_or_equal' => 'Waktu penarikan tidak boleh mendahului waktu penyimpanan (stored_at).',
            ]);

            // Jika status diubah menjadi withdrawn tapi tidak ada withdrawn_at, set default ke "now"
            if ($validated['status'] === 'withdrawn' && empty($validated['withdrawn_at'])) {
                $validated['withdrawn_at'] = now();
            }

            // Jika status dirubah kembali menjadi active (misal undo), kosongkan withdrawn_at
            if ($validated['status'] === 'active') {
                $validated['withdrawn_at'] = null;
            }

            $sample->update($validated);

            return response()->json([
                'status'  => 'success',
                'message' => "Sampel {$sample->sample_code} berhasil diperbarui.",
                'data'    => $sample,
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data untuk update tidak valid.',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => "Sampel dengan ID {$id} tidak ditemukan.",
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui sampel: ' . $e->getMessage(),
            ], 500);
        }
    }
}
