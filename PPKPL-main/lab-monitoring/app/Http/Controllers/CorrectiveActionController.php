<?php

namespace App\Http\Controllers;

use App\Models\IncidentTicket;
use App\Models\CorrectiveAction;
use App\Models\Notification;
use App\Models\User;
use App\Http\Requests\StoreCorrectiveActionRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CorrectiveActionController extends Controller
{
    public function index($ticketId)
    {
        $actions = CorrectiveAction::where('incident_ticket_id', $ticketId)
            ->with('recorder')
            ->orderBy('created_at', 'asc') 
            ->get();

        return response()->json(['status' => 'success', 'data' => $actions]);
    }

    public function store(StoreCorrectiveActionRequest $request, $ticketId)
    {
        $ticket = IncidentTicket::findOrFail($ticketId);

        // Validasi status tiket
        if ($ticket->status === 'closed') {
            return response()->json(['status' => 'error', 'message' => 'Tiket sudah ditutup'], 422);
        }

        return DB::transaction(function () use ($request, $ticket) {
            $action = CorrectiveAction::create([
                'incident_ticket_id' => $ticket->id,
                'description' => $request->description,
                'recorded_by' => Auth::id() ?? 1, 
                'created_at' => now()
            ]);

            $ticket->update(['status' => 'dalam_penanganan']);

            $manager = User::where('role', 'admin')->first();
            if ($manager) {
                Notification::create([
                    'user_id' => $manager->id,
                    'message' => "Update: Tiket #{$ticket->id} dalam penanganan oleh " . Auth::user()->name,
                    'is_read' => false
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tindakan korektif berhasil dicatat',
                'data' => $action->load('recorder')
            ]);
        });
    }
}