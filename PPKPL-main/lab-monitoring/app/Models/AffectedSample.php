<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AffectedSample extends Model
{
    protected $fillable = [
        'incident_id',
        'sample_code',
        'product_name',
        'batch_number',
        'quantity',
        'notes',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }
}
