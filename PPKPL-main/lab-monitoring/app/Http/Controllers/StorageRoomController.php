<?php

namespace App\Http\Controllers;

use App\Models\StorageRoom;
use Illuminate\Http\Request;

class StorageRoomController extends Controller
{
    /**
     * Display a listing of storage rooms.
     */
    public function index()
    {
        $rooms = StorageRoom::all();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Data ruang penyimpanan berhasil diambil',
            'data' => $rooms
        ]);
    }
}
