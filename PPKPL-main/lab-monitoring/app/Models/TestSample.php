<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class TestSample extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal (mass assignment).
     */
    protected $fillable = [
        'sample_code',
        'sample_name',
        'storage_room_id',
        'stored_at',
        'withdrawn_at',
        'status',
    ];

    /**
     * Cast tipe data kolom agar otomatis dikonversi.
     */
    protected $casts = [
        'stored_at'     => 'datetime',
        'withdrawn_at'  => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relasi
    // -------------------------------------------------------------------------

    /**
     * Sampel uji disimpan di satu ruang penyimpanan.
     */
    public function storageRoom(): BelongsTo
    {
        return $this->belongsTo(StorageRoom::class, 'storage_room_id');
    }

    // -------------------------------------------------------------------------
    // Query Scopes
    // -------------------------------------------------------------------------

    /**
     * Scope untuk memfilter hanya sampel yang masih aktif di ruangan
     * (belum ditarik / status = 'active').
     *
     * Penggunaan: TestSample::active()->get();
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
