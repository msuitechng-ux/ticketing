<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: sans-serif;
            padding: 10px;
            margin: 0;
        }
        .ticket-container {
            width: 100%;
            border: 2px solid #222;
            border-radius: 8px;
            padding: 15px;
        }
        .header {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .details {
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
        }
        .qr {
            text-align: center;
            margin-top: 20px;
        }
        img.qr-img {
            width: 140px;
            height: 140px;
        }
    </style>
</head>
<body>

<div class="ticket-container">
    <div class="header">Graduation Ticket</div>

    <div class="details">
        <p><strong>Ceremony:</strong> {{ $ticket->ceremony->name }}</p>
        <p><strong>Ticket Code:</strong> {{ $ticket->ticket_code }}</p>
        <p><strong>Guest Name:</strong> {{ $ticket->guest_name ?: 'Unassigned' }}</p>
    </div>

    <div class="qr">
        <img class="qr-img" src="{{ $qrBase64 }}" alt="QR Code" />
    </div>
</div>

</body>
</html>
