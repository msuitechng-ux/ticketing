<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    /**
     * Display a listing of the user's tickets.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $graduate = $user->graduates()->with('ceremony')->first();

        if (! $graduate) {
            return Inertia::render('Student/Tickets/Index', [
                'tickets' => [],
                'graduate' => null,
            ]);
        }

        $tickets = $graduate->tickets()
            ->with('ceremony')
            ->latest()
            ->get();

        return Inertia::render('Student/Tickets/Index', [
            'tickets' => $tickets,
            'graduate' => $graduate,
        ]);
    }

    /**
     * Display the specified ticket.
     */
    public function show(Ticket $ticket): Response
    {
        $this->authorize('view', $ticket);

        $ticket->load(['graduate.user', 'graduate.ceremony', 'scannedBy']);

        return Inertia::render('Student/Tickets/Show', [
            'ticket' => $ticket,
        ]);
    }

    /**
     * Update guest information for a ticket.
     */
    public function updateGuest(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'nullable|email|max:255',
        ]);

        $ticket->update($validated);

        return redirect()->back()
            ->with('success', 'Guest information updated successfully.');
    }

    /**
     * Download the QR code for a ticket.
     */
    public function downloadQr(Ticket $ticket): StreamedResponse
    {
        $this->authorize('view', $ticket);

        if (! $ticket->qr_code_path || ! Storage::disk('public')->exists($ticket->qr_code_path)) {
            abort(404, 'QR code not found.');
        }

        return Storage::disk('public')->download(
            $ticket->qr_code_path,
            "ticket-{$ticket->ticket_code}.png"
        );
    }

    /**
     * Download all QR codes for the user's tickets as a ZIP.
     */
    public function downloadAllQr(Request $request): StreamedResponse
    {
        $user = $request->user();
        $graduate = $user->graduates()->first();

        if (! $graduate) {
            abort(404, 'No graduate profile found.');
        }

        $tickets = $graduate->tickets()->where('status', 'Active')->get();

        if ($tickets->isEmpty()) {
            abort(404, 'No active tickets found.');
        }

        $zip = new \ZipArchive;
        $zipFileName = storage_path("app/temp/tickets-{$graduate->student_id}-".time().'.zip');

        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        if ($zip->open($zipFileName, \ZipArchive::CREATE) !== true) {
            abort(500, 'Could not create ZIP file.');
        }

        foreach ($tickets as $ticket) {
            if ($ticket->qr_code_path && Storage::disk('public')->exists($ticket->qr_code_path)) {
                $filePath = Storage::disk('public')->path($ticket->qr_code_path);
                $zip->addFile($filePath, "ticket-{$ticket->ticket_code}.png");
            }
        }

        $zip->close();

        return response()->streamDownload(function () use ($zipFileName) {
            echo file_get_contents($zipFileName);
            unlink($zipFileName);
        }, "tickets-{$graduate->student_id}.zip");
    }

    /**
     * Cancel a ticket (admin only).
     */
    public function cancel(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        if ($ticket->is_scanned) {
            return redirect()->back()
                ->with('error', 'Cannot cancel a ticket that has already been scanned.');
        }

        $ticket->update(['status' => 'Cancelled']);

        return redirect()->back()
            ->with('success', 'Ticket cancelled successfully.');
    }
}
