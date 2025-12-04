<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketRequest extends Model
{
    /** @use HasFactory<\Database\Factories\TicketRequestFactory> */
    use HasFactory;

    protected $fillable = [
        'graduate_id',
        'ceremony_id',
        'requested_quantity',
        'approved_quantity',
        'reason',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function graduate(): BelongsTo
    {
        return $this->belongsTo(Graduate::class);
    }

    public function ceremony(): BelongsTo
    {
        return $this->belongsTo(Ceremony::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'Pending');
    }

    public function scopeApproved($query)
    {
        return $query->whereIn('status', ['Approved', 'Partially Approved']);
    }

    public function scopeWaitlisted($query)
    {
        return $query->where('status', 'Waitlisted');
    }

    public function scopeDenied($query)
    {
        return $query->where('status', 'Denied');
    }

    public function getIsApprovedAttribute(): bool
    {
        return in_array($this->status, ['Approved', 'Partially Approved']);
    }

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'Pending';
    }

    public function getIsWaitlistedAttribute(): bool
    {
        return $this->status === 'Waitlisted';
    }
}
