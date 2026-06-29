<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ConditionDataController;
use App\Http\Controllers\StorageRoomController;
use App\Http\Controllers\CorrectiveActionController;
use App\Http\Controllers\AffectedSampleController;
use App\Http\Controllers\TestSampleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Endpoint untuk rekam dan lihat data kondisi (Condition Data)
Route::get('/condition-data', [ConditionDataController::class, 'index']);
Route::post('/condition-data', [ConditionDataController::class, 'store']);

// Endpoint sederhana untuk mengambil data ruang penyimpanan
Route::get('/storage-rooms', [StorageRoomController::class, 'index']);

// Endpoint untuk mengambil daftar notifikasi
Route::get('/notifications', function () {
    return response()->json([
        'status' => 'success',
        'data' => \App\Models\Notification::latest()->get()
    ]);
});

// Endpoint untuk Test Samples (US 3.4)
Route::get('/test-samples', [TestSampleController::class, 'index']);
Route::post('/test-samples', [TestSampleController::class, 'store']);
Route::put('/test-samples/{id}', [TestSampleController::class, 'update']);

// Endpoint untuk Affected Samples (US 3.4)
Route::get('/affected-samples', [AffectedSampleController::class, 'index']);
Route::get('/affected-samples/{conditionDataId}', [AffectedSampleController::class, 'show']);
