<?php

namespace App\Services;

use App\Models\ConditionData;
use App\Models\IncidentTicket; // Menggunakan IncidentTicket yang benar
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;

class IncidentReportService
{
    /**
     * Generate a standardized PDF file for the given closed incident.
     *
     * @param  IncidentTicket  $incident  An Incident model with all relations eager-loaded.
     * @return string                     Absolute path to the generated PDF file.
     */
    public function generatePDF(IncidentTicket $incident): string
    {
        // 1. Retrieve condition data within the deviation period for the affected room.
        $conditionData = $this->buildConditionTable($incident);

        // 2. Resolve a standardized file name: Incident_<ID>_<Date>.pdf
        $fileName = $this->resolveFileName($incident);

        // 3. Compile all sections into the view data array.
        $data = [
            'incident'          => $incident,
            'conditionData'     => $conditionData,
            'correctiveActions' => $this->buildCorrectiveActionsLog($incident),
            'affectedSamples'   => $this->buildAffectedSamplesList($incident),
            // Gunakan relasi creator karena tabel kelompok Anda tidak punya kolom assignedQA
            'qaManager'         => $incident->creator,
            'generatedAt'       => now()->format('d F Y, H:i') . ' WIB',
        ];

        // 4. Render the Blade view and stream it through DomPDF.
        $pdf = Pdf::loadView('incidents.pdf.report', $data)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => false,
                'defaultFont'          => 'sans-serif',
            ]);

        // 5. Save to a temporary path and return it.
        $storagePath = storage_path("app/public/incident_reports/{$fileName}");

        if (! is_dir(dirname($storagePath))) {
            mkdir(dirname($storagePath), 0755, true);
        }

        $pdf->save($storagePath);

        Log::info("[IncidentReportService] PDF generated for Incident #{$incident->id}", [
            'file' => $storagePath,
        ]);

        return $storagePath;
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Fetch condition_data entries for the affected room
     * that fall within the incident's deviation period.
     */
    private function buildConditionTable(IncidentTicket $incident): \Illuminate\Support\Collection
    {
        return ConditionData::where('storage_room_id', $incident->storage_room_id)
            ->whereBetween('created_at', [
                $incident->conditionData->created_at ?? $incident->created_at, // Waktu awal insiden terdeteksi
                $incident->updated_at ?? now(), // Waktu tiket ditutup/terakhir diupdate
            ])
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Return all corrective actions belonging to the incident,
     * sorted chronologically.
     */
    private function buildCorrectiveActionsLog(IncidentTicket $incident): \Illuminate\Support\Collection
    {
        // Diubah menjadi created_at karena tabel teman Anda tidak memakai performed_at
        return $incident->correctiveActions
            ->sortBy('created_at');
    }

    /**
     * Return all affected samples associated with the incident.
     */
    private function buildAffectedSamplesList(IncidentTicket $incident): \Illuminate\Support\Collection
    {
        // Tabel affected_samples tidak ada di proyek Anda, jadi kita kembalikan 
        // koleksi kosong agar Blade template PDF tidak error (undefined).
        return collect();
    }

    /**
     * Build the standardized PDF file name.
     * Format: Incident_<ID>_<YYYY-MM-DD>.pdf
     */
    private function resolveFileName(IncidentTicket $incident): string
    {
        $date = now()->format('Y-m-d');
        return "Incident_{$incident->id}_{$date}.pdf";
    }
}
