<?php

namespace App\Models;

use App\Models\IncidentTicket;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    protected $fillable = [
        'incident_ticket_id',
        'storage_room_id',
        'reported_by',
        'assigned_qa_id',
        'title',
        'description',
        'deviation_level',
        'status',
        'deviation_start',
        'deviation_end',
        'closing_notes',
    ];

    protected $casts = [
        'deviation_start' => 'datetime',
        'deviation_end'   => 'datetime',
    ];

    // ─── Business Logic ───────────────────────────────────────────

    /**
     * Determine whether the incident ticket has been officially closed.
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    // ─── Relationships ────────────────────────────────────────────

    public function storageRoom(): BelongsTo
    {
        return $this->belongsTo(StorageRoom::class);
    }

    public function reportedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function assignedQA(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_qa_id');
    }

    public function correctiveActions(): HasMany
    {
        return $this->hasMany(CorrectiveAction::class);
    }

    public function affectedSamples(): HasMany
    {
        return $this->hasMany(AffectedSample::class);
    }

    public function incidentReports(): HasMany
    {
        return $this->hasMany(IncidentReport::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(IncidentTicket::class, 'incident_ticket_id');
    }
}
