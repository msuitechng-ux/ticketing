<?php

namespace App\Services;

use App\Models\Ticket;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class QrCodeService
{
    /**
     * Generate a QR code for a ticket
     */
    public function generateQrCode(Ticket $ticket): string
    {
        $qrData = $this->encodeTicketData($ticket);

        $result = Builder::create()
            ->writer(new PngWriter)
            ->writerOptions([])
            ->data($qrData)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::High)
            ->size(300)
            ->margin(10)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();

        $fileName = "qr-codes/{$ticket->ceremony_id}/{$ticket->ticket_code}.png";

        Storage::disk('public')->put($fileName, $result->getString());

        return $fileName;
    }

    /**
     * Encode ticket data for QR code
     */
    private function encodeTicketData(Ticket $ticket): string
    {
        $data = [
            'ticket_id' => $ticket->id,
            'ticket_code' => $ticket->ticket_code,
            'ceremony_id' => $ticket->ceremony_id,
            'graduate_id' => $ticket->graduate_id,
            'type' => $ticket->ticket_type,
            'checksum' => $this->generateChecksum($ticket),
        ];

        return json_encode($data);
    }

    /**
     * Decode QR code data
     */
    public function decodeQrData(string $qrData): ?array
    {
        try {
            $data = json_decode($qrData, true);

            if (! isset($data['ticket_code']) || ! isset($data['checksum'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Validate QR code data
     */
    public function validateQrCode(string $qrData): bool
    {
        $data = $this->decodeQrData($qrData);

        if (! $data) {
            return false;
        }

        $ticket = Ticket::find($data['ticket_id']);

        if (! $ticket) {
            return false;
        }

        // Verify checksum
        $expectedChecksum = $this->generateChecksum($ticket);

        return $expectedChecksum === $data['checksum'];
    }

    /**
     * Generate a checksum for ticket verification
     */
    private function generateChecksum(Ticket $ticket): string
    {
        $string = implode('|', [
            $ticket->id,
            $ticket->ticket_code,
            $ticket->ceremony_id,
            $ticket->graduate_id,
            config('app.key'),
        ]);

        return hash('sha256', $string);
    }

    /**
     * Get QR code URL
     */
    public function getQrCodeUrl(Ticket $ticket): ?string
    {
        if (! $ticket->qr_code_path) {
            return null;
        }

        return Storage::disk('public')->url($ticket->qr_code_path);
    }

    /**
     * Regenerate QR code for a ticket
     */
    public function regenerateQrCode(Ticket $ticket): string
    {
        // Delete old QR code if exists
        if ($ticket->qr_code_path && Storage::disk('public')->exists($ticket->qr_code_path)) {
            Storage::disk('public')->delete($ticket->qr_code_path);
        }

        return $this->generateQrCode($ticket);
    }
}
