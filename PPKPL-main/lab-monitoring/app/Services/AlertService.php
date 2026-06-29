<?php

namespace App\Services;

use App\Mail\DeviationAlertMail;
use App\Models\ConditionData;
use App\Models\IncidentTicket;
use App\Models\Notification;
use App\Models\StorageRoom;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * AlertService
 *
 * Bertanggung jawab atas seluruh logika notifikasi dan alert
 * yang dipicu secara otomatis setelah data kondisi disimpan.
 *
 * Klasifikasi Deviasi (sesuai PRD US 3.2):
 * ┌─────────────┬─────────────┬────────────────────────────────────────────────┐
 * │ Indikator   │ Level       │ Tindakan                                       │
 * ├─────────────┼─────────────┼────────────────────────────────────────────────┤
 * │ green       │ -           │ Tidak ada tindakan                             │
 * │ yellow      │ Level 1     │ Notifikasi in-app → Petugas saja               │
 * │ red         │ Level 2     │ Tiket insiden + Notifikasi → Petugas & Manajer │
 * │             │             │ + Email otomatis → Semua Manajer               │
 * └─────────────┴─────────────┴────────────────────────────────────────────────┘
 */
class AlertService
{
    /**
     * Entry point utama. Dipanggil dari ConditionDataController::store()
     * segera setelah data kondisi berhasil disimpan ke database.
     *
     * @param  ConditionData $conditionData  Data kondisi yang baru disimpan
     * @param  StorageRoom   $room           Ruang penyimpanan terkait
     */
    public function processAlert(ConditionData $conditionData, StorageRoom $room): void
    {
        match ($conditionData->indicator_color) {
            'red'    => $this->handleLevelTwoCritical($conditionData, $room),
            'yellow' => $this->handleLevelOneWarning($conditionData, $room),
            default  => null, // 'green': kondisi normal, tidak ada tindakan
        };
    }

    // =========================================================================
    // LEVEL 2 — KRITIS (Red)
    // =========================================================================

    /**
     * Menangani deviasi Level 2 (Kritis):
     * 1. Membuat tiket insiden baru di tabel incident_tickets
     * 2. Membuat notifikasi in-app untuk petugas penginput
     * 3. Membuat notifikasi in-app untuk setiap Manajer Laboratorium
     * 4. Mengirim email alert ke setiap Manajer Laboratorium
     *
     * @param  ConditionData $conditionData
     * @param  StorageRoom   $room
     */
    private function handleLevelTwoCritical(ConditionData $conditionData, StorageRoom $room): void
    {
        // ── 1. Buat Tiket Insiden ─────────────────────────────────────────────
        $ticket = IncidentTicket::create([
            'storage_room_id'   => $room->id,
            'condition_data_id' => $conditionData->id,
            'deviation_level'   => '2',
            'status'            => 'open',
        ]);

        Log::info("[AlertService] Tiket insiden #{$ticket->id} dibuat untuk ruang '{$room->room_name}'.");

        // ── 2. Susun pesan notifikasi ─────────────────────────────────────────
        $notificationMessage = sprintf(
            '[KRITIS - Tiket #%d] Deviasi kondisi terdeteksi di Ruang %s pada %s. ' .
            'Suhu: %.1f°C (Batas Maks: %.1f°C) | Kelembaban: %.1f%% (Batas Maks: %.1f%%). ' .
            'Status tiket: TERBUKA. Harap segera lakukan tindakan korektif.',
            $ticket->id,
            $room->room_name,
            now()->format('d/m/Y H:i:s'),
            $conditionData->temperature,
            $room->temp_limit,
            $conditionData->humidity,
            $room->humidity_limit,
        );

        // ── 3. Notifikasi in-app → Petugas penginput ─────────────────────────
        Notification::create([
            'user_id' => $conditionData->inputted_by,
            'message' => $notificationMessage,
        ]);

        // ── 4. Notifikasi in-app + Email → Semua Manajer Laboratorium ─────────
        $managers = User::where('role', 'admin')->get();

        if ($managers->isEmpty()) {
            Log::warning("[AlertService] Tidak ada pengguna dengan role 'manager' ditemukan. Email tidak dapat dikirim.");
        }

        foreach ($managers as $manager) {
            // Notifikasi in-app untuk manajer
            Notification::create([
                'user_id' => $manager->id,
                'message' => $notificationMessage,
            ]);

            // Kirim email alert ke manajer
            $this->sendEmailToManager($manager, $conditionData, $ticket, $room);
        }
    }

    /**
     * Mengirim email alert ke Manajer Laboratorium.
     * Error pengiriman dicatat di log tanpa menghentikan proses utama.
     *
     * @param  User           $manager
     * @param  ConditionData  $conditionData
     * @param  IncidentTicket $ticket
     * @param  StorageRoom    $room
     */
    private function sendEmailToManager(
        User          $manager,
        ConditionData $conditionData,
        IncidentTicket $ticket,
        StorageRoom   $room
    ): void {
        try {
            Mail::to($manager->email)
                ->send(new DeviationAlertMail($conditionData, $ticket, $room));

            Log::info("[AlertService] Email alert berhasil dikirim ke Manajer '{$manager->name}' ({$manager->email}).");
        } catch (\Exception $e) {
            // Gagal kirim email dicatat tanpa menyebabkan proses store() gagal (graceful degradation)
            Log::error(
                "[AlertService] Gagal mengirim email alert ke '{$manager->email}'. " .
                "Tiket #{$ticket->id}. Error: " . $e->getMessage()
            );
        }
    }

    // =========================================================================
    // LEVEL 1 — PERINGATAN (Yellow)
    // =========================================================================

    /**
     * Menangani deviasi Level 1 (Peringatan):
     * Hanya membuat notifikasi in-app untuk petugas penginput.
     * Tidak membuat tiket insiden. Tidak mengirim email.
     *
     * @param  ConditionData $conditionData
     * @param  StorageRoom   $room
     */
    private function handleLevelOneWarning(ConditionData $conditionData, StorageRoom $room): void
    {
        Notification::create([
            'user_id' => $conditionData->inputted_by,
            'message' => sprintf(
                '[PERINGATAN] Kondisi di Ruang %s mendekati batas toleransi pada %s. ' .
                'Suhu: %.1f°C (Batas Maks: %.1f°C) | Kelembaban: %.1f%% (Batas Maks: %.1f%%). ' .
                'Harap pantau kondisi secara berkala.',
                $room->room_name,
                now()->format('d/m/Y H:i:s'),
                $conditionData->temperature,
                $room->temp_limit,
                $conditionData->humidity,
                $room->humidity_limit,
            ),
        ]);

        Log::info("[AlertService] Notifikasi peringatan Level 1 dikirim ke user ID {$conditionData->inputted_by} untuk ruang '{$room->room_name}'.");
    }
}
