<?php

use App\Http\Controllers\CeremonyController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EntryVerificationController;
use App\Http\Controllers\GraduateController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketRequestController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        $user = auth()->user();

        if ($user->hasRole('Admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('Student')) {
            return redirect()->route('student.dashboard');
        }

        if ($user->hasRole('Security Staff')) {
            return redirect()->route('security.dashboard');
        }

        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Admin Routes
    Route::middleware(['role:Admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::resource('ceremonies', CeremonyController::class);
        Route::resource('graduates', GraduateController::class);

        Route::get('graduates-import', [GraduateController::class, 'import'])->name('graduates.import');
        Route::post('graduates-import', [GraduateController::class, 'processImport'])->name('graduates.process-import');

        Route::get('ticket-requests', [TicketRequestController::class, 'index'])->name('ticket-requests.index');
        Route::get('ticket-requests/{ticketRequest}', [TicketRequestController::class, 'show'])->name('ticket-requests.show');
        Route::post('ticket-requests/{ticketRequest}/process', [TicketRequestController::class, 'process'])->name('ticket-requests.process');
        Route::post('ticket-requests/batch-process', [TicketRequestController::class, 'batchProcess'])->name('ticket-requests.batch-process');
    });

    // Student Routes
    Route::middleware(['role:Student'])->prefix('student')->name('student.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'student'])->name('dashboard');

        Route::get('tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::put('tickets/{ticket}/guest', [TicketController::class, 'updateGuest'])->name('tickets.update-guest');
        Route::get('tickets/{ticket}/download-qr', [TicketController::class, 'downloadQr'])->name('tickets.download-qr');
        Route::get('tickets-download-all', [TicketController::class, 'downloadAllQr'])->name('tickets.download-all-qr');

        Route::get('ticket-requests', [TicketRequestController::class, 'myRequests'])->name('ticket-requests.my-requests');
        Route::post('ticket-requests', [TicketRequestController::class, 'store'])->name('ticket-requests.store');
    });

    // Security Staff Routes
    Route::middleware(['role:Security Staff'])->prefix('security')->name('security.')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'security'])->name('dashboard');

        Route::get('scanner', [EntryVerificationController::class, 'scanner'])->name('scanner');
        Route::post('verify', [EntryVerificationController::class, 'verify'])->name('verify');
        Route::post('verify-by-code', [EntryVerificationController::class, 'verifyByCode'])->name('verify-by-code');
        Route::get('stats', [EntryVerificationController::class, 'stats'])->name('stats');

        Route::get('entry-logs', [EntryVerificationController::class, 'logs'])->name('entry-logs');
        Route::get('fraud-cases', [EntryVerificationController::class, 'fraudCases'])->name('fraud-cases');
    });

    // Shared Routes (Admin and Event Staff)
    Route::middleware(['role:Admin|Event Staff'])->group(function () {
        Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->name('tickets.cancel');
    });
});

require __DIR__.'/settings.php';
