<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\IncidentTicket;
use App\Models\ConditionData;
use App\Models\StorageRoom;
use App\Models\CorrectiveAction;
use App\Http\Controllers\IncidentReportController;

Route::get('/', function () {
    return redirect('/monitoring');
});

Route::get('/monitoring', function () {
    return view('monitoring');
});

Route::get('/monitoring/affected-samples', function () {
    return view('affected-samples');
});

Route::get('/monitoring/incidents/{id?}', function ($id = null) {
    if ($id) {
        $ticket = IncidentTicket::with(['correctiveActions.recorder', 'creator', 'conditionData.room'])
            ->findOrFail($id);

        return view('incidents', compact('ticket'));
    }

    $tickets = IncidentTicket::with(['conditionData.room', 'creator'])->latest()->get();
    return view('incidents', compact('tickets'));
});

Route::get('/api/storage-rooms', function () {
    return response()->json([
        'status' => 'success',
        'data' => StorageRoom::all()
    ]);
});

Route::get('/api/condition-data', function () {
    return response()->json([
        'status' => 'success',
        'data' => ConditionData::with(['room', 'user', 'incidentTicket'])->latest()->get()
    ]);
});

Route::post('/api/condition-data', function (Request $request) {
    $room = App\Models\StorageRoom::find($request->storage_room_id);

    $temp = (float) $request->temperature;
    $hum = (float) $request->humidity;
    $tLimit = (float) $room->temp_limit;
    $hLimit = (float) $room->humidity_limit;

    $color = 'green';

    if ($temp > ($tLimit + 2.0) || $hum > ($hLimit + 10.0)) {
        $color = 'red';
    } elseif ($temp > $tLimit || $hum > $hLimit || $temp >= ($tLimit - 2.0)) {
        $color = 'yellow';
    }

    $condition = App\Models\ConditionData::create([
        'storage_room_id' => $request->storage_room_id,
        'inputted_by'     => $request->inputted_by,
        'temperature'     => $temp,
        'humidity'        => $hum,
        'indicator_color' => $color,
    ]);

    if ($color !== 'green') {
        App\Models\IncidentTicket::create([
            'condition_data_id' => $condition->id,
            'storage_room_id'   => $condition->storage_room_id,
            'status'            => 'open',
        ]);
    }

    return response()->json(['status' => 'success', 'data' => $condition]);
});

// web.php

// Cari bagian ini di web.php dan ganti total:

Route::post('/api/incident-tickets/{id}/actions', function (Request $request, $id) {
    // Validasi agar tidak menyimpan string kosong
    if (!$request->description) {
        return response()->json(['status' => 'error', 'message' => 'Deskripsi tidak boleh kosong'], 422);
    }

    $ticket = IncidentTicket::findOrFail($id);

    // 1. Simpan tindakan korektif
    $action = CorrectiveAction::create([
        'incident_ticket_id' => $id,
        'description'        => $request->description, // Pastikan ini sesuai dengan JSON yang dikirim
        'recorded_by'        => 1, // Pastikan ada user dengan ID 1 di tabel users Anda
    ]);

    // 2. Ubah status hanya jika masih 'open'
    if ($ticket->status === 'open') {
        $ticket->update(['status' => 'dalam_penanganan']);
    }

    return response()->json(['status' => 'success', 'data' => $action]);
});

// Rute ini khusus digunakan saat user menekan tombol "Selesaikan Insiden"
Route::post('/api/incident-tickets/{id}/close', function ($id) {
    $ticket = IncidentTicket::findOrFail($id);

    $ticket->update([
        'status' => 'closed'
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Tiket berhasil ditutup'
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

// Jika ingin menggunakan Controller untuk halaman index dan show:
Route::get('/incidents-list', [IncidentReportController::class, 'index'])->name('incidents.index');
