<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntryLog extends Model
{
    /** @use HasFactory<\Database\Factories\EntryLogFactory> */
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'ceremony_id',
        'scanned_by',
        'scanned_at',
        'entry_point',
        'verification_status',
        'notes',
        'device_info',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function ceremony(): BelongsTo
    {
        return $this->belongsTo(Ceremony::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function scopeSuccessful($query)
    {
        return $query->where('verification_status', 'Success');
    }

    public function scopeFraudAttempts($query)
    {
        return $query->where('verification_status', 'Fraud Attempt');
    }

    public function scopeDuplicates($query)
    {
        return $query->where('verification_status', 'Duplicate');
    }

    public function scopeInvalid($query)
    {
        return $query->where('verification_status', 'Invalid');
    }
}
