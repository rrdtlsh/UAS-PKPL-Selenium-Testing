<?php

namespace App\Mail;

use App\Models\ConditionData;
use App\Models\IncidentTicket;
use App\Models\StorageRoom;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * DeviationAlertMail
 *
 * Mailable untuk mengirim notifikasi email ke Manajer Laboratorium
 * ketika terdeteksi deviasi kondisi Level 2 (Kritis).
 *
 * Dipanggil oleh AlertService::handleLevelTwoCritical()
 */
class DeviationAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Konstruktor menerima tiga model sebagai konteks email.
     * Properti public secara otomatis tersedia di Blade view.
     *
     * @param  ConditionData  $conditionData  Data kondisi pemicu
     * @param  IncidentTicket $ticket         Tiket insiden yang baru dibuat
     * @param  StorageRoom    $room           Ruang penyimpanan yang mengalami deviasi
     */
    public function __construct(
        public readonly ConditionData  $conditionData,
        public readonly IncidentTicket $ticket,
        public readonly StorageRoom    $room,
    ) {}

    /**
     * Konfigurasi envelope (subject dan metadata) email.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: sprintf(
                '[PERINGATAN KRITIS] Tiket Insiden #%d — Deviasi Kondisi Ruang %s',
                $this->ticket->id,
                $this->room->room_name,
            ),
        );
    }

    /**
     * Konfigurasi konten email menggunakan Blade view.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.deviation-alert',
        );
    }

    /**
     * Lampiran email (tidak ada dalam implementasi ini).
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
