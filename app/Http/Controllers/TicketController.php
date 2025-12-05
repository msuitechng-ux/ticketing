<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketController extends Controller
{
    public function __construct(
        private QrCodeService $qrCodeService
    ) {}

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
  
    public function downloadQr(Ticket $ticket)
    {
        //$this->authorize('view', $ticket);

        // Clear ANY previous output
        if (ob_get_length()) {
            ob_end_clean();
        }

        $pdfContent = $this->qrCodeService->generateTicketPdf($ticket);
        $filename = "ticket-{$ticket->ticket_code}.pdf";

        return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="ticket_'.$ticket->id.'.pdf'
            ]);

    }



    /**
     * Download all QR codes for the user's active tickets as a ZIP file.
     */
    public function downloadAllQr(Request $request): StreamedResponse
    {
        $user = $request->user();
        $graduate = $user->graduates()->firstOrFail();

        $tickets = $graduate->tickets()->where('status', 'Active')->get();

        if ($tickets->isEmpty()) {
            abort(404, 'No active tickets found.');
        }

        // Ensure QR codes exist
        foreach ($tickets as $ticket) {
            if (! $ticket->qr_code_path || ! Storage::disk('public')->exists($ticket->qr_code_path)) {
                $qrPath = $this->qrCodeService->regenerateQrCode($ticket);
                $ticket->update(['qr_code_path' => $qrPath]);
            }
        }

        // Prepare ZIP folder
        $tempFolder = storage_path('app/temp');
        if (! is_dir($tempFolder)) {
            mkdir($tempFolder, 0755, true);
        }

        $zipFileName = "{$tempFolder}/tickets-{$graduate->student_id}-" . time() . ".zip";

        // Clear ANY accidental output (critical)
        if (ob_get_length()) {
            ob_end_clean();
        }

        $zip = new \ZipArchive;

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

        // Return ZIP file for download
        return response()->streamDownload(function () use ($zipFileName) {

            // Prevent Chrome/PDF corruption
            if (ob_get_length()) {
                ob_end_clean();
            }

            readfile($zipFileName);

            // Delete temp zip
            @unlink($zipFileName);

        }, "tickets.zip", [
            "Content-Type" => "application/zip",
            "Content-Disposition" => "attachment; filename=\"tickets.zip\"",
            "Cache-Control" => "no-cache, must-revalidate",
            "Pragma" => "no-cache",
        ]);
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
