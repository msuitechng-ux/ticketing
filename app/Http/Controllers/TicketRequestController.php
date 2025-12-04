<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProcessTicketRequestRequest;
use App\Http\Requests\StoreTicketRequestRequest;
use App\Models\TicketRequest;
use App\Services\TicketRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TicketRequestController extends Controller
{
    public function __construct(
        private TicketRequestService $requestService
    ) {}

    /**
     * Display a listing of ticket requests (Admin view).
     */
    public function index(Request $request): Response
    {
        $query = TicketRequest::query()
            ->with(['graduate.user', 'graduate.ceremony', 'reviewer']);

        if ($request->filled('ceremony_id')) {
            $query->where('ceremony_id', $request->ceremony_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('graduate', function ($q) use ($request) {
                $q->where('student_name', 'like', "%{$request->search}%")
                    ->orWhere('student_id', 'like', "%{$request->search}%");
            });
        }

        $ticketRequests = $query->latest()->paginate(20);

        return Inertia::render('Admin/TicketRequests/Index', [
            'ticketRequests' => $ticketRequests,
            'filters' => $request->only(['ceremony_id', 'status', 'search']),
            'stats' => [
                'pending' => TicketRequest::where('status', 'Pending')->count(),
                'approved' => TicketRequest::whereIn('status', ['Approved', 'Partially Approved'])->count(),
                'denied' => TicketRequest::where('status', 'Denied')->count(),
                'waitlisted' => TicketRequest::where('status', 'Waitlisted')->count(),
            ],
        ]);
    }

    /**
     * Display the user's ticket requests (Student view).
     */
    public function myRequests(Request $request): Response
    {
        $user = $request->user();
        $graduate = $user->graduates()->with('ceremony')->first();

        if (! $graduate) {
            return Inertia::render('Student/TicketRequests/Index', [
                'ticketRequests' => [],
                'graduate' => null,
                'canRequestMore' => false,
            ]);
        }

        $ticketRequests = $graduate->ticketRequests()
            ->with('reviewer')
            ->latest()
            ->get();

        $activeRequest = $graduate->activeTicketRequest;
        $ceremony = $graduate->ceremony;
        $canRequestMore = ! $activeRequest &&
                         (! $ceremony->ticket_request_deadline || now()->isBefore($ceremony->ticket_request_deadline));

        return Inertia::render('Student/TicketRequests/Index', [
            'ticketRequests' => $ticketRequests,
            'graduate' => $graduate,
            'canRequestMore' => $canRequestMore,
        ]);
    }

    /**
     * Store a newly created ticket request.
     */
    public function store(StoreTicketRequestRequest $request): RedirectResponse
    {
        $user = $request->user();
        $graduate = $user->graduates()->first();

        if (! $graduate) {
            return redirect()->back()
                ->with('error', 'No graduate profile found.');
        }

        try {
            $ticketRequest = $this->requestService->createRequest(
                $graduate,
                $request->requested_quantity,
                $request->reason
            );

            return redirect()->route('ticket-requests.my-requests')
                ->with('success', 'Ticket request submitted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified ticket request.
     */
    public function show(TicketRequest $ticketRequest): Response
    {
        $ticketRequest->load(['graduate.user', 'graduate.ceremony', 'reviewer']);

        return Inertia::render('Admin/TicketRequests/Show', [
            'ticketRequest' => $ticketRequest,
        ]);
    }

    /**
     * Process a ticket request (approve, deny, or waitlist).
     */
    public function process(ProcessTicketRequestRequest $request, TicketRequest $ticketRequest): RedirectResponse
    {
        $user = $request->user();

        try {
            $result = match ($request->action) {
                'approve' => $this->requestService->approveRequest(
                    $ticketRequest,
                    $request->approved_quantity,
                    $user,
                    $request->admin_notes
                ),
                'deny' => $this->requestService->denyRequest(
                    $ticketRequest,
                    $user,
                    $request->admin_notes
                ),
                'waitlist' => $this->requestService->waitlistRequest(
                    $ticketRequest,
                    $user,
                    $request->admin_notes
                ),
            };

            return redirect()->route('ticket-requests.show', $result)
                ->with('success', "Ticket request {$request->action}d successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Process multiple ticket requests in batch.
     */
    public function batchProcess(Request $request): RedirectResponse
    {
        $request->validate([
            'decisions' => 'required|array',
            'decisions.*.request_id' => 'required|exists:ticket_requests,id',
            'decisions.*.action' => 'required|in:approve,deny,waitlist',
            'decisions.*.approved_quantity' => 'required_if:decisions.*.action,approve|integer|min:0',
            'decisions.*.admin_notes' => 'nullable|string',
        ]);

        $user = $request->user();
        $results = $this->requestService->processBatchRequests($request->decisions, $user);

        $totalProcessed = count($results['approved']) + count($results['denied']) + count($results['waitlisted']);
        $totalErrors = count($results['errors']);

        if ($totalErrors > 0) {
            return redirect()->back()
                ->with('warning', "Processed {$totalProcessed} requests with {$totalErrors} errors.")
                ->with('errors', $results['errors']);
        }

        return redirect()->back()
            ->with('success', "Successfully processed {$totalProcessed} ticket requests.");
    }
}
