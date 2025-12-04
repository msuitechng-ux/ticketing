<?php

namespace App\Services;

use App\Models\EntryLog;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EntryVerificationService
{
    public function __construct(
        private QrCodeService $qrCodeService
    ) {}

    /**
     * Verify a ticket using QR code data
     */
    public function verifyTicket(string $qrData, User $scanner, ?string $entryPoint = null, ?string $deviceInfo = null): array
    {
        return DB::transaction(function () use ($qrData, $scanner, $entryPoint, $deviceInfo) {
            // Decode QR data
            $decodedData = $this->qrCodeService->decodeQrData($qrData);

            if (! $decodedData) {
                return $this->createFailureResponse('Invalid QR code format', null, $scanner, $entryPoint, $deviceInfo, 'Invalid');
            }

            // Find ticket
            $ticket = Ticket::find($decodedData['ticket_id']);

            if (! $ticket) {
                return $this->createFailureResponse('Ticket not found', null, $scanner, $entryPoint, $deviceInfo, 'Invalid');
            }

            // Validate checksum
            if (! $this->qrCodeService->validateQrCode($qrData)) {
                return $this->createFailureResponse('Invalid ticket signature - possible fraud', $ticket, $scanner, $entryPoint, $deviceInfo, 'Fraud Attempt');
            }

            // Check if already scanned
            if ($ticket->is_scanned) {
                return $this->createFailureResponse('Ticket already used', $ticket, $scanner, $entryPoint, $deviceInfo, 'Duplicate');
            }

            // Check ticket status
            if ($ticket->status !== 'Active') {
                return $this->createFailureResponse("Ticket is {$ticket->status}", $ticket, $scanner, $entryPoint, $deviceInfo, 'Invalid');
            }

            // Mark ticket as scanned
            $ticket->update([
                'is_scanned' => true,
                'scanned_at' => now(),
                'scanned_by' => $scanner->id,
                'status' => 'Used',
            ]);

            // Increment tickets_used for graduate
            $ticket->graduate->increment('tickets_used');

            // Create success entry log
            $this->createEntryLog($ticket, $scanner, $entryPoint, $deviceInfo, 'Success');

            return [
                'success' => true,
                'message' => 'Ticket verified successfully',
                'ticket' => $ticket->load(['graduate.user', 'graduate.ceremony']),
                'graduate_name' => $ticket->graduate->student_name,
                'guest_name' => $ticket->guest_name,
                'ticket_type' => $ticket->ticket_type,
            ];
        });
    }

    /**
     * Verify ticket by ticket code (manual entry)
     */
    public function verifyByTicketCode(string $ticketCode, User $scanner, ?string $entryPoint = null, ?string $deviceInfo = null): array
    {
        return DB::transaction(function () use ($ticketCode, $scanner, $entryPoint, $deviceInfo) {
            $ticket = Ticket::where('ticket_code', $ticketCode)->first();

            if (! $ticket) {
                return $this->createFailureResponse('Ticket not found', null, $scanner, $entryPoint, $deviceInfo, 'Invalid');
            }

            if ($ticket->is_scanned) {
                return $this->createFailureResponse('Ticket already used', $ticket, $scanner, $entryPoint, $deviceInfo, 'Duplicate');
            }

            if ($ticket->status !== 'Active') {
                return $this->createFailureResponse("Ticket is {$ticket->status}", $ticket, $scanner, $entryPoint, $deviceInfo, 'Invalid');
            }

            // Mark ticket as scanned
            $ticket->update([
                'is_scanned' => true,
                'scanned_at' => now(),
                'scanned_by' => $scanner->id,
                'status' => 'Used',
            ]);

            $ticket->graduate->increment('tickets_used');

            $this->createEntryLog($ticket, $scanner, $entryPoint, $deviceInfo, 'Success');

            return [
                'success' => true,
                'message' => 'Ticket verified successfully',
                'ticket' => $ticket->load(['graduate.user', 'graduate.ceremony']),
                'graduate_name' => $ticket->graduate->student_name,
                'guest_name' => $ticket->guest_name,
                'ticket_type' => $ticket->ticket_type,
            ];
        });
    }

    /**
     * Create a failure response and log the attempt
     */
    private function createFailureResponse(string $message, ?Ticket $ticket, User $scanner, ?string $entryPoint, ?string $deviceInfo, string $verificationStatus): array
    {
        if ($ticket) {
            $this->createEntryLog($ticket, $scanner, $entryPoint, $deviceInfo, $verificationStatus, $message);
        }

        return [
            'success' => false,
            'message' => $message,
            'verification_status' => $verificationStatus,
            'ticket' => $ticket,
        ];
    }

    /**
     * Create an entry log record
     */
    private function createEntryLog(Ticket $ticket, User $scanner, ?string $entryPoint, ?string $deviceInfo, string $verificationStatus, ?string $notes = null): EntryLog
    {
        return EntryLog::create([
            'ticket_id' => $ticket->id,
            'ceremony_id' => $ticket->ceremony_id,
            'scanned_by' => $scanner->id,
            'scanned_at' => now(),
            'entry_point' => $entryPoint,
            'verification_status' => $verificationStatus,
            'device_info' => $deviceInfo,
            'notes' => $notes,
        ]);
    }

    /**
     * Get entry statistics for a ceremony
     */
    public function getEntryStats(int $ceremonyId): array
    {
        $logs = EntryLog::where('ceremony_id', $ceremonyId);

        return [
            'total_scans' => $logs->count(),
            'successful_entries' => $logs->clone()->where('verification_status', 'Success')->count(),
            'duplicate_attempts' => $logs->clone()->where('verification_status', 'Duplicate')->count(),
            'fraud_attempts' => $logs->clone()->where('verification_status', 'Fraud Attempt')->count(),
            'invalid_tickets' => $logs->clone()->where('verification_status', 'Invalid')->count(),
            'recent_entries' => $logs->clone()
                ->with(['ticket.graduate', 'scanner'])
                ->latest('scanned_at')
                ->limit(10)
                ->get(),
        ];
    }

    /**
     * Get fraud cases for review
     */
    public function getFraudCases(int $ceremonyId): array
    {
        return EntryLog::query()
            ->where('ceremony_id', $ceremonyId)
            ->where('verification_status', 'Fraud Attempt')
            ->with(['ticket.graduate.user', 'scanner'])
            ->latest('scanned_at')
            ->get()
            ->toArray();
    }
}
