<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorrectiveAction extends Model
{
    // Gunakan kolom dari versi teman Anda
    protected $fillable = [
        'incident_ticket_id',
        'description',
        'recorded_by',
        'created_at'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Relasi dari versi teman Anda
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(IncidentTicket::class, 'incident_ticket_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Alias agar kode dari AI (yang menggunakan performedByUser) tetap jalan
    public function performedByUser(): BelongsTo
    {
        return $this->recorder();
    }
}
