<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ceremony extends Model
{
    /** @use HasFactory<\Database\Factories\CeremonyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'ceremony_date',
        'venue',
        'venue_address',
        'total_capacity',
        'base_tickets_per_graduate',
        'ticket_request_deadline',
        'redistribution_deadline',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'ceremony_date' => 'datetime',
            'ticket_request_deadline' => 'datetime',
            'redistribution_deadline' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function graduates(): HasMany
    {
        return $this->hasMany(Graduate::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketRequests(): HasMany
    {
        return $this->hasMany(TicketRequest::class);
    }

    public function entryLogs(): HasMany
    {
        return $this->hasMany(EntryLog::class);
    }

    public function getTotalTicketsAllocatedAttribute(): int
    {
        return $this->graduates()->sum('tickets_allocated') + $this->graduates()->sum('extra_tickets_approved');
    }

    public function getTotalTicketsUsedAttribute(): int
    {
        return $this->graduates()->sum('tickets_used');
    }

    public function getUtilizationRateAttribute(): float
    {
        $allocated = $this->getTotalTicketsAllocatedAttribute();

        return $allocated > 0 ? ($this->getTotalTicketsUsedAttribute() / $allocated) * 100 : 0;
    }
}
