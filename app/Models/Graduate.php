<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Graduate extends Model
{
    /** @use HasFactory<\Database\Factories\GraduateFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ceremony_id',
        'student_id',
        'student_name',
        'degree_level',
        'faculty',
        'department',
        'tickets_allocated',
        'extra_tickets_requested',
        'extra_tickets_approved',
        'tickets_used',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function ceremony(): BelongsTo
    {
        return $this->belongsTo(Ceremony::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketRequests(): HasMany
    {
        return $this->hasMany(TicketRequest::class);
    }

    public function activeTicketRequest(): HasOne
    {
        return $this->hasOne(TicketRequest::class)
            ->whereIn('status', ['Pending', 'Waitlisted'])
            ->latest();
    }

    public function getTotalTicketsAttribute(): int
    {
        return $this->tickets_allocated + $this->extra_tickets_approved;
    }

    public function getTicketsUnusedAttribute(): int
    {
        return $this->getTotalTicketsAttribute() - $this->tickets_used;
    }

    public function getAttendanceRateAttribute(): float
    {
        $total = $this->getTotalTicketsAttribute();

        return $total > 0 ? ($this->tickets_used / $total) * 100 : 0;
    }

    public function getRequestStatusAttribute(): string
    {
        $activeRequest = $this->activeTicketRequest;

        if (! $activeRequest) {
            return $this->extra_tickets_requested > 0 ? 'Processed' : 'No Request';
        }

        return $activeRequest->status;
    }
}
