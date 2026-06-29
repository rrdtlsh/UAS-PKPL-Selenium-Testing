<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ConditionData extends Model
{
    use HasFactory;

    protected $fillable = [
        'storage_room_id',
        'inputted_by',
        'temperature',
        'humidity',
        'indicator_color',
    ];

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    /**
     * Data kondisi milik satu ruang penyimpanan.
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(StorageRoom::class, 'storage_room_id');
    }

    /**
     * Data kondisi diinput oleh satu pengguna.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inputted_by');
    }

    /**
     * Data kondisi dapat memiliki satu tiket insiden (jika Level 2 / Kritis).
     */
    public function incidentTicket()
    {
        return $this->hasOne(IncidentTicket::class, 'condition_data_id');
    }
}
