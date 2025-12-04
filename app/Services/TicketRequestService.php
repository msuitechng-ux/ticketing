<?php

namespace App\Services;

use App\Models\Graduate;
use App\Models\TicketRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TicketRequestService
{
    public function __construct(
        private TicketAllocationService $allocationService
    ) {}

    /**
     * Create a new ticket request
     */
    public function createRequest(Graduate $graduate, int $quantity, ?string $reason = null): TicketRequest
    {
        // Check if there's already a pending/waitlisted request
        $existingRequest = $graduate->activeTicketRequest;

        if ($existingRequest) {
            throw new \Exception('You already have an active ticket request.');
        }

        // Check ceremony deadline
        $ceremony = $graduate->ceremony;
        if ($ceremony->ticket_request_deadline && now()->isAfter($ceremony->ticket_request_deadline)) {
            throw new \Exception('The ticket request deadline has passed.');
        }

        $request = TicketRequest::create([
            'graduate_id' => $graduate->id,
            'ceremony_id' => $graduate->ceremony_id,
            'requested_quantity' => $quantity,
            'reason' => $reason,
            'status' => 'Pending',
        ]);

        $graduate->increment('extra_tickets_requested', $quantity);

        return $request;
    }

    /**
     * Approve a ticket request
     */
    public function approveRequest(TicketRequest $request, int $approvedQuantity, User $reviewer, ?string $adminNotes = null): TicketRequest
    {
        return DB::transaction(function () use ($request, $approvedQuantity, $reviewer, $adminNotes) {
            $status = $approvedQuantity >= $request->requested_quantity ? 'Approved' : 'Partially Approved';

            $request->update([
                'approved_quantity' => $approvedQuantity,
                'status' => $status,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'admin_notes' => $adminNotes,
            ]);

            // Allocate the approved tickets
            if ($approvedQuantity > 0) {
                $this->allocationService->allocateExtraTickets($request->graduate, $approvedQuantity);
            }

            return $request->fresh();
        });
    }

    /**
     * Deny a ticket request
     */
    public function denyRequest(TicketRequest $request, User $reviewer, ?string $adminNotes = null): TicketRequest
    {
        $request->update([
            'status' => 'Denied',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        return $request;
    }

    /**
     * Waitlist a ticket request
     */
    public function waitlistRequest(TicketRequest $request, User $reviewer, ?string $adminNotes = null): TicketRequest
    {
        $request->update([
            'status' => 'Waitlisted',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'admin_notes' => $adminNotes,
        ]);

        return $request;
    }

    /**
     * Process pending requests in batch
     */
    public function processBatchRequests(array $decisions, User $reviewer): array
    {
        $results = [
            'approved' => [],
            'denied' => [],
            'waitlisted' => [],
            'errors' => [],
        ];

        foreach ($decisions as $decision) {
            try {
                $request = TicketRequest::findOrFail($decision['request_id']);

                match ($decision['action']) {
                    'approve' => $results['approved'][] = $this->approveRequest(
                        $request,
                        $decision['approved_quantity'] ?? $request->requested_quantity,
                        $reviewer,
                        $decision['admin_notes'] ?? null
                    ),
                    'deny' => $results['denied'][] = $this->denyRequest(
                        $request,
                        $reviewer,
                        $decision['admin_notes'] ?? null
                    ),
                    'waitlist' => $results['waitlisted'][] = $this->waitlistRequest(
                        $request,
                        $reviewer,
                        $decision['admin_notes'] ?? null
                    ),
                    default => throw new \Exception('Invalid action'),
                };
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'request_id' => $decision['request_id'] ?? null,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get request statistics for a ceremony
     */
    public function getRequestStats(int $ceremonyId): array
    {
        $requests = TicketRequest::where('ceremony_id', $ceremonyId);

        return [
            'total_requests' => $requests->count(),
            'pending' => $requests->clone()->where('status', 'Pending')->count(),
            'approved' => $requests->clone()->whereIn('status', ['Approved', 'Partially Approved'])->count(),
            'denied' => $requests->clone()->where('status', 'Denied')->count(),
            'waitlisted' => $requests->clone()->where('status', 'Waitlisted')->count(),
            'total_requested_tickets' => $requests->clone()->sum('requested_quantity'),
            'total_approved_tickets' => $requests->clone()->sum('approved_quantity'),
        ];
    }
}
