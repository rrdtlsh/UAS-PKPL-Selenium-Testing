<?php

namespace App\Http\Controllers;

use App\Models\IncidentTicket; // Wajib menggunakan IncidentTicket bawaan kelompok
use App\Services\IncidentReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Auth;

class IncidentReportController extends Controller
{
    public function __construct(
        private readonly IncidentReportService $reportService
    ) {}

    public function index(): JsonResponse
    {
        $incidents = IncidentTicket::with([
            'conditionData.room',
            'creator',
        ])->latest()->get();

        return response()->json([
            'status' => 'success',
            'data'   => $incidents,
        ]);
    }

    public function show(int $incidentId)
    {
        $ticket = IncidentTicket::with([
            'conditionData.room',
            'creator',
            'correctiveActions.recorder',
        ])->findOrFail($incidentId);

        return view('incidents', compact('ticket'));
    }

    public function exportPdf(int $incidentId)
    {
        // Tarik data menggunakan IncidentTicket dan relasi bawaan kelompok
        $incident = IncidentTicket::with([
            'conditionData.room',
            'creator',
            'correctiveActions.recorder',
        ])->findOrFail($incidentId);

        // Validasi AC-2: Cek status tertutup
        if (strtolower($incident->status) !== 'closed' && strtolower($incident->status) !== 'tertutup') {
            return response()->json([
                'status'  => 'error',
                'message' => 'Laporan hanya dapat diekspor setelah tiket insiden ditutup.',
            ], 422);
        }

        // Generate PDF
        $pdfPath = $this->reportService->generatePDF($incident);

        // Download file PDF
        return response()->download(
            $pdfPath,
            basename($pdfPath),
            ['Content-Type' => 'application/pdf']
        )->deleteFileAfterSend(true);
    }
}
