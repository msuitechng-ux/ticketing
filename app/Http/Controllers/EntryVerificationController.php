<?php

namespace App\Http\Controllers;

use App\Models\Ceremony;
use App\Models\EntryLog;
use App\Services\EntryVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EntryVerificationController extends Controller
{
    public function __construct(
        private EntryVerificationService $verificationService
    ) {}

    /**
     * Display the scanner interface.
     */
    public function scanner(Request $request): Response
    {
        $user = $request->user();
        $activeCeremonies = Ceremony::query()
            ->where('is_active', true)
            ->where('ceremony_date', '>=', now()->subDays(1))
            ->get(['id', 'name', 'ceremony_date', 'venue']);

        return Inertia::render('Security/Scanner', [
            'ceremonies' => $activeCeremonies,
            'entryPoints' => [
                'Main Entrance',
                'East Entrance',
                'West Entrance',
                'VIP Entrance',
            ],
        ]);
    }

    /**
     * Verify a ticket using QR code data.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'qr_data' => 'required|string',
            'entry_point' => 'nullable|string|max:255',
            'device_info' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $result = $this->verificationService->verifyTicket(
            $request->qr_data,
            $user,
            $request->entry_point,
            $request->device_info
        );

        return response()->json($result);
    }

    /**
     * Verify a ticket using manual ticket code entry.
     */
    public function verifyByCode(Request $request): JsonResponse
    {
        $request->validate([
            'ticket_code' => 'required|string',
            'entry_point' => 'nullable|string|max:255',
            'device_info' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $result = $this->verificationService->verifyByTicketCode(
            $request->ticket_code,
            $user,
            $request->entry_point,
            $request->device_info
        );

        return response()->json($result);
    }

    /**
     * Display entry logs for a ceremony.
     */
    public function logs(Request $request): Response
    {
        $query = EntryLog::query()
            ->with(['ticket.graduate', 'ceremony', 'scanner']);

        if ($request->filled('ceremony_id')) {
            $query->where('ceremony_id', $request->ceremony_id);
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->verification_status);
        }

        if ($request->filled('entry_point')) {
            $query->where('entry_point', $request->entry_point);
        }

        if ($request->filled('date')) {
            $query->whereDate('scanned_at', $request->date);
        }

        $logs = $query->latest('scanned_at')->paginate(30);

        $ceremonies = Ceremony::query()
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Security/EntryLogs', [
            'logs' => $logs,
            'ceremonies' => $ceremonies,
            'filters' => $request->only(['ceremony_id', 'verification_status', 'entry_point', 'date']),
            'stats' => [
                'total_scans' => EntryLog::count(),
                'successful' => EntryLog::where('verification_status', 'Success')->count(),
                'duplicate' => EntryLog::where('verification_status', 'Duplicate')->count(),
                'fraud' => EntryLog::where('verification_status', 'Fraud Attempt')->count(),
                'invalid' => EntryLog::where('verification_status', 'Invalid')->count(),
            ],
        ]);
    }

    /**
     * Get real-time entry statistics for a ceremony.
     */
    public function stats(Request $request): JsonResponse
    {
        $request->validate([
            'ceremony_id' => 'required|exists:ceremonies,id',
        ]);

        $stats = $this->verificationService->getEntryStats($request->ceremony_id);

        return response()->json($stats);
    }

    /**
     * Get fraud cases for review.
     */
    public function fraudCases(Request $request): Response
    {
        $query = EntryLog::query()
            ->where('verification_status', 'Fraud Attempt')
            ->with(['ticket.graduate.user', 'ceremony', 'scanner']);

        if ($request->filled('ceremony_id')) {
            $query->where('ceremony_id', $request->ceremony_id);
        }

        $fraudCases = $query->latest('scanned_at')->paginate(20);

        $ceremonies = Ceremony::query()
            ->where('is_active', true)
            ->get(['id', 'name']);

        return Inertia::render('Security/FraudCases', [
            'fraudCases' => $fraudCases,
            'ceremonies' => $ceremonies,
            'filters' => $request->only(['ceremony_id']),
        ]);
    }
}
