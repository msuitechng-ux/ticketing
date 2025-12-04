<?php

namespace App\Services;

use App\Models\Ceremony;
use App\Models\Graduate;
use App\Models\Ticket;
use Illuminate\Support\Str;

class TicketAllocationService
{
    public function __construct(
        private QrCodeService $qrCodeService
    ) {}

    /**
     * Allocate base tickets to a graduate
     */
    public function allocateBaseTickets(Graduate $graduate): array
    {
        $ceremony = $graduate->ceremony;
        $ticketsToAllocate = $ceremony->base_tickets_per_graduate;

        $tickets = [];

        for ($i = 0; $i < $ticketsToAllocate; $i++) {
            $ticket = $this->createTicket($graduate, 'Base');
            $tickets[] = $ticket;
        }

        $graduate->update([
            'tickets_allocated' => $ticketsToAllocate,
        ]);

        return $tickets;
    }

    /**
     * Allocate extra approved tickets to a graduate
     */
    public function allocateExtraTickets(Graduate $graduate, int $quantity): array
    {
        $tickets = [];

        for ($i = 0; $i < $quantity; $i++) {
            $ticket = $this->createTicket($graduate, 'Extra');
            $tickets[] = $ticket;
        }

        $graduate->increment('extra_tickets_approved', $quantity);

        return $tickets;
    }

    /**
     * Create a single ticket with unique code and QR code
     */
    private function createTicket(Graduate $graduate, string $type = 'Base'): Ticket
    {
        $ticketCode = $this->generateUniqueTicketCode();

        $ticket = Ticket::create([
            'graduate_id' => $graduate->id,
            'ceremony_id' => $graduate->ceremony_id,
            'ticket_code' => $ticketCode,
            'ticket_type' => $type,
            'status' => 'Active',
        ]);

        // Generate QR code
        $qrCodePath = $this->qrCodeService->generateQrCode($ticket);
        $ticket->update(['qr_code_path' => $qrCodePath]);

        return $ticket;
    }

    /**
     * Generate a unique ticket code
     */
    private function generateUniqueTicketCode(): string
    {
        do {
            $code = strtoupper(Str::random(10));
        } while (Ticket::where('ticket_code', $code)->exists());

        return $code;
    }

    /**
     * Redistribute unused tickets to waitlisted graduates
     */
    public function redistributeUnusedTickets(Ceremony $ceremony): array
    {
        // Find unused tickets
        $unusedTickets = Ticket::query()
            ->where('ceremony_id', $ceremony->id)
            ->where('status', 'Active')
            ->where('is_scanned', false)
            ->whereHas('graduate', function ($query) {
                $query->whereRaw('tickets_used = 0');
            })
            ->get();

        if ($unusedTickets->isEmpty()) {
            return [];
        }

        // Get waitlisted graduates ordered by request date
        $waitlistedGraduates = Graduate::query()
            ->where('ceremony_id', $ceremony->id)
            ->whereHas('ticketRequests', function ($query) {
                $query->where('status', 'Waitlisted');
            })
            ->with(['ticketRequests' => function ($query) {
                $query->where('status', 'Waitlisted')->oldest();
            }])
            ->get();

        $redistributed = [];

        foreach ($waitlistedGraduates as $graduate) {
            if ($unusedTickets->isEmpty()) {
                break;
            }

            $request = $graduate->ticketRequests->first();
            $neededQuantity = $request->requested_quantity - $request->approved_quantity;

            $ticketsToRedistribute = $unusedTickets->splice(0, min($neededQuantity, $unusedTickets->count()));

            foreach ($ticketsToRedistribute as $ticket) {
                $ticket->update([
                    'graduate_id' => $graduate->id,
                    'status' => 'Redistributed',
                ]);

                $graduate->increment('extra_tickets_approved');
            }

            $request->update([
                'approved_quantity' => $request->approved_quantity + $ticketsToRedistribute->count(),
                'status' => $request->approved_quantity >= $request->requested_quantity ? 'Approved' : 'Partially Approved',
            ]);

            $redistributed[] = [
                'graduate' => $graduate,
                'tickets_received' => $ticketsToRedistribute->count(),
            ];
        }

        return $redistributed;
    }

    /**
     * Get allocation statistics for a ceremony
     */
    public function getAllocationStats(Ceremony $ceremony): array
    {
        $graduates = $ceremony->graduates;

        return [
            'total_graduates' => $graduates->count(),
            'total_tickets_allocated' => $graduates->sum('tickets_allocated') + $graduates->sum('extra_tickets_approved'),
            'total_tickets_used' => $graduates->sum('tickets_used'),
            'base_tickets_allocated' => $graduates->sum('tickets_allocated'),
            'extra_tickets_allocated' => $graduates->sum('extra_tickets_approved'),
            'unused_tickets' => Ticket::where('ceremony_id', $ceremony->id)
                ->where('status', 'Active')
                ->where('is_scanned', false)
                ->count(),
            'utilization_rate' => $ceremony->utilization_rate,
        ];
    }
}
