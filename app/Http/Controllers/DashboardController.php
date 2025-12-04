<?php

namespace App\Http\Controllers;

use App\Models\Ceremony;
use App\Models\EntryLog;
use App\Models\Graduate;
use App\Models\Ticket;
use App\Models\TicketRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function admin(Request $request): Response
    {
        $totalCeremonies = Ceremony::count();
        $activeCeremonies = Ceremony::where('is_active', true)->count();
        $totalGraduates = Graduate::count();
        $totalTickets = Ticket::count();
        $ticketsUsed = Ticket::where('is_scanned', true)->count();
        $pendingRequests = TicketRequest::where('status', 'Pending')->count();

        $upcomingCeremonies = Ceremony::query()
            ->where('ceremony_date', '>=', now())
            ->with('graduates')
            ->withCount(['graduates', 'tickets'])
            ->orderBy('ceremony_date')
            ->limit(5)
            ->get();

        $recentTicketRequests = TicketRequest::query()
            ->with(['graduate.user', 'graduate.ceremony'])
            ->where('status', 'Pending')
            ->latest()
            ->limit(10)
            ->get();

        $recentEntries = EntryLog::query()
            ->with(['ticket.graduate', 'ceremony', 'scanner'])
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        $ceremonyStats = Ceremony::query()
            ->where('is_active', true)
            ->get()
            ->map(function ($ceremony) {
                return [
                    'id' => $ceremony->id,
                    'name' => $ceremony->name,
                    'ceremony_date' => $ceremony->ceremony_date,
                    'total_graduates' => $ceremony->graduates_count,
                    'total_tickets_allocated' => $ceremony->getTotalTicketsAllocatedAttribute(),
                    'total_tickets_used' => $ceremony->getTotalTicketsUsedAttribute(),
                    'utilization_rate' => $ceremony->getUtilizationRateAttribute(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_ceremonies' => $totalCeremonies,
                'active_ceremonies' => $activeCeremonies,
                'total_graduates' => $totalGraduates,
                'total_tickets' => $totalTickets,
                'tickets_used' => $ticketsUsed,
                'ticket_utilization_rate' => $totalTickets > 0 ? round(($ticketsUsed / $totalTickets) * 100, 2) : 0,
                'pending_requests' => $pendingRequests,
            ],
            'upcomingCeremonies' => $upcomingCeremonies,
            'recentTicketRequests' => $recentTicketRequests,
            'recentEntries' => $recentEntries,
            'ceremonyStats' => $ceremonyStats,
        ]);
    }

    /**
     * Display the student dashboard.
     */
    public function student(Request $request): Response
    {
        $user = $request->user();
        $graduate = $user->graduates()->with('ceremony')->first();

        if (! $graduate) {
            return Inertia::render('Student/Dashboard', [
                'graduate' => null,
                'tickets' => [],
                'ticketRequests' => [],
                'stats' => null,
            ]);
        }

        $tickets = $graduate->tickets()
            ->with('ceremony')
            ->get();

        $ticketRequests = $graduate->ticketRequests()
            ->with('reviewer')
            ->latest()
            ->get();

        $activeRequest = $graduate->activeTicketRequest;
        $ceremony = $graduate->ceremony;
        $canRequestMore = ! $activeRequest &&
                         (! $ceremony->ticket_request_deadline || now()->isBefore($ceremony->ticket_request_deadline));

        return Inertia::render('Student/Dashboard', [
            'graduate' => $graduate,
            'ceremony' => $ceremony,
            'tickets' => $tickets,
            'ticketRequests' => $ticketRequests,
            'stats' => [
                'total_tickets' => $graduate->getTotalTicketsAttribute(),
                'tickets_used' => $graduate->tickets_used,
                'tickets_available' => $graduate->getTotalTicketsAttribute() - $graduate->tickets_used,
                'attendance_rate' => $graduate->getAttendanceRateAttribute(),
                'request_status' => $graduate->getRequestStatusAttribute(),
                'can_request_more' => $canRequestMore,
            ],
        ]);
    }

    /**
     * Display the security staff dashboard.
     */
    public function security(Request $request): Response
    {
        $user = $request->user();

        $todayScans = EntryLog::query()
            ->where('scanned_by', $user->id)
            ->whereDate('scanned_at', today())
            ->count();

        $todaySuccessful = EntryLog::query()
            ->where('scanned_by', $user->id)
            ->whereDate('scanned_at', today())
            ->where('verification_status', 'Success')
            ->count();

        $todayFraud = EntryLog::query()
            ->where('scanned_by', $user->id)
            ->whereDate('scanned_at', today())
            ->where('verification_status', 'Fraud Attempt')
            ->count();

        $recentScans = EntryLog::query()
            ->with(['ticket.graduate', 'ceremony'])
            ->where('scanned_by', $user->id)
            ->latest('scanned_at')
            ->limit(20)
            ->get();

        $activeCeremonies = Ceremony::query()
            ->where('is_active', true)
            ->where('ceremony_date', '>=', now()->subDays(1))
            ->withCount('tickets')
            ->get();

        return Inertia::render('Security/Dashboard', [
            'stats' => [
                'today_scans' => $todayScans,
                'today_successful' => $todaySuccessful,
                'today_fraud' => $todayFraud,
                'today_duplicate' => $todayScans - $todaySuccessful - $todayFraud,
            ],
            'recentScans' => $recentScans,
            'activeCeremonies' => $activeCeremonies,
        ]);
    }
}
