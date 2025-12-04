<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    /** @use HasFactory<\Database\Factories\TicketFactory> */
    use HasFactory;

    protected $fillable = [
        'graduate_id',
        'ceremony_id',
        'ticket_code',
        'qr_code_path',
        'guest_name',
        'guest_email',
        'ticket_type',
        'status',
        'is_scanned',
        'scanned_at',
        'scanned_by',
    ];

    protected function casts(): array
    {
        return [
            'is_scanned' => 'boolean',
            'scanned_at' => 'datetime',
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

    public function scannedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    public function entryLogs(): HasMany
    {
        return $this->hasMany(EntryLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'Used');
    }

    public function scopeUnused($query)
    {
        return $query->where('status', 'Active')->where('is_scanned', false);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('ticket_type', $type);
    }
}
