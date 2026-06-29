<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\IncidentTicket;
use App\Models\ConditionData;
use App\Models\StorageRoom;
use App\Models\CorrectiveAction;
use App\Http\Controllers\IncidentReportController;
use App\Http\Controllers\Auth\AuthController;
use App\Services\ConditionDataService;
use App\Services\AlertService;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Guest Routes — Hanya bisa diakses sebelum login
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', [AuthController::class, 'showLogin'])
        ->name('login');

    Route::post('/login', [AuthController::class, 'login'])
        ->name('login.process');

});

/*
|--------------------------------------------------------------------------
| Authenticated Routes — Wajib login untuk mengakses
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Redirect root ke monitoring
    Route::get('/', function () {
        return redirect()->route('monitoring');
    });

    /*
    |----------------------------------------------------------------------
    | Halaman Utama — Bisa diakses oleh semua user yang sudah login
    |----------------------------------------------------------------------
    */
    Route::get('/monitoring', function () {
        return view('monitoring');
    })->name('monitoring');

    Route::get('/monitoring/affected-samples', function () {
        return view('affected-samples');
    })->name('affected-samples');

    Route::get('/monitoring/incidents/{id?}', function ($id = null) {
        if ($id) {
            $ticket = IncidentTicket::with(['correctiveActions.recorder', 'creator', 'conditionData.room'])
                ->findOrFail($id);

            return view('incidents', compact('ticket'));
        }

        $tickets = IncidentTicket::with(['conditionData.room', 'creator'])->latest()->get();
        return view('incidents', compact('tickets'));
    })->name('incidents');

    /*
    |----------------------------------------------------------------------
    | API Routes (dipanggil dari JavaScript)
    | Tetap dijaga oleh auth middleware via session cookie
    |----------------------------------------------------------------------
    */
    Route::get('/api/storage-rooms', function () {
        return response()->json([
            'status' => 'success',
            'data'   => StorageRoom::all(),
        ]);
    });

    Route::get('/api/condition-data', function () {
        return response()->json([
            'status' => 'success',
            'data'   => ConditionData::with(['room', 'user', 'incidentTicket'])->latest()->get(),
        ]);
    });

    Route::post('/api/condition-data', function (Request $request, ConditionDataService $conditionDataService, AlertService $alertService) {
        // Validasi input — konsisten dengan StoreConditionDataRequest di ConditionDataController
        $validated = $request->validate([
            'storage_room_id' => 'required|exists:storage_rooms,id',
            'temperature'     => 'required|numeric|min:-50|max:100',
            'humidity'        => 'required|numeric|min:0|max:100',
        ], [
            'storage_room_id.required' => 'Ruang penyimpanan wajib dipilih.',
            'storage_room_id.exists'   => 'Ruang penyimpanan tidak valid dalam sistem.',
            'temperature.required'     => 'Suhu ruangan wajib diisi.',
            'temperature.numeric'      => 'Suhu ruangan harus berupa format angka.',
            'temperature.min'          => 'Suhu ruangan tidak boleh kurang dari -50 derajat.',
            'temperature.max'          => 'Suhu ruangan tidak boleh melebihi 100 derajat.',
            'humidity.required'        => 'Kelembaban ruangan wajib diisi.',
            'humidity.numeric'         => 'Kelembaban ruangan harus berupa format angka.',
            'humidity.min'             => 'Tingkat kelembaban minimum adalah 0%.',
            'humidity.max'             => 'Tingkat kelembaban maksimum adalah 100%.',
        ]);

        // inputted_by selalu dari Auth::id() — tidak boleh dikirim dari frontend
        $validated['inputted_by'] = Auth::id();

        $room = StorageRoom::findOrFail($validated['storage_room_id']);

        // Satu-satunya sumber logika kalkulasi warna: ConditionDataService
        $condition = $conditionDataService->store($validated, $room);

        // Proses alert dan auto-create IncidentTicket jika diperlukan
        $alertService->processAlert($condition, $room);

        return response()->json(['status' => 'success', 'data' => $condition]);
    });

    Route::post('/api/incident-tickets/{id}/actions', function (Request $request, $id) {
        if (! $request->description) {
            return response()->json(['status' => 'error', 'message' => 'Deskripsi tidak boleh kosong'], 422);
        }

        $ticket = IncidentTicket::findOrFail($id);

        $action = CorrectiveAction::create([
            'incident_ticket_id' => $id,
            'description'        => $request->description,
            'recorded_by'        => Auth::id(),
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'dalam_penanganan']);
        }

        return response()->json(['status' => 'success', 'data' => $action]);
    });

    Route::post('/api/incident-tickets/{id}/close', function ($id) {
        $ticket = IncidentTicket::findOrFail($id);
        $ticket->update(['status' => 'closed']);

        return response()->json([
            'status'  => 'success',
            'message' => 'Tiket berhasil ditutup',
        ]);
    });

    Route::delete('/api/incident-tickets/{id}', function ($id) {
        $ticket = App\Models\IncidentTicket::findOrFail($id);
        App\Models\CorrectiveAction::where('incident_ticket_id', $id)->delete();
        $ticket->delete();

        return response()->json(['status' => 'success', 'message' => 'Tiket berhasil dibuang!']);
    });

    Route::get('/monitoring/incidents/{id}/export-pdf', [IncidentReportController::class, 'exportPdf'])
        ->name('incidents.export-pdf');

    Route::get('/incidents-list', [IncidentReportController::class, 'index'])
        ->name('incidents.index');

    /*
    |----------------------------------------------------------------------
    | Admin-Only Routes — Hanya bisa diakses oleh role 'admin'
    | Digunakan untuk TC-S05: Petugas tidak boleh mengakses halaman Admin
    |----------------------------------------------------------------------
    */
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');
    });
});