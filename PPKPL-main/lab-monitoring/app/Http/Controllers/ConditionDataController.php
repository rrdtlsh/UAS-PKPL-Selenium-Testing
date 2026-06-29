<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreConditionDataRequest;
use App\Models\ConditionData;
use App\Models\StorageRoom;
use App\Services\AlertService;
use App\Services\ConditionDataService;

class ConditionDataController extends Controller
{
    public function __construct(
        private readonly ConditionDataService $conditionDataService,
        private readonly AlertService         $alertService,
    ) {}

    public function index()
    {
        $data = ConditionData::with(['room', 'user'])->latest()->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kondisi ruang berhasil diambil',
            'data'    => $data,
        ]);
    }

    public function store(StoreConditionDataRequest $request)
    {
        $validated = $request->validated();
        $room = StorageRoom::findOrFail($validated['storage_room_id']);

        // 1. Simpan data kondisi (kalkulasi warna dilakukan di Service)
        $conditionData = $this->conditionDataService->store($validated, $room);

        // 2. Proses alert otomatis berdasarkan indikator warna (US 3.2)
        $this->alertService->processAlert($conditionData, $room);

        return response()->json([
            'status'  => 'success',
            'message' => 'Data kondisi ruang berhasil direkam',
            'data'    => $conditionData->load(['room', 'user', 'incidentTicket']),
        ], 201);
    }
}