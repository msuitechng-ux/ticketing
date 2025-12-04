<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGraduateRequest;
use App\Http\Requests\UpdateGraduateRequest;
use App\Models\Ceremony;
use App\Models\Graduate;
use App\Models\User;
use App\Services\TicketAllocationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GraduateController extends Controller
{
    public function __construct(
        private TicketAllocationService $allocationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Graduate::query()
            ->with(['user', 'ceremony'])
            ->withCount('tickets');

        if ($request->filled('ceremony_id')) {
            $query->where('ceremony_id', $request->ceremony_id);
        }

        if ($request->filled('faculty')) {
            $query->where('faculty', $request->faculty);
        }

        if ($request->filled('degree_level')) {
            $query->where('degree_level', $request->degree_level);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('student_name', 'like', "%{$request->search}%")
                    ->orWhere('student_id', 'like', "%{$request->search}%");
            });
        }

        $graduates = $query->latest()->paginate(20);

        $ceremonies = Ceremony::query()->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Graduates/Index', [
            'graduates' => $graduates,
            'ceremonies' => $ceremonies,
            'filters' => $request->only(['ceremony_id', 'faculty', 'degree_level', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $ceremonies = Ceremony::query()->where('is_active', true)->get(['id', 'name']);
        $users = User::query()->role('Student')->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Graduates/Create', [
            'ceremonies' => $ceremonies,
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGraduateRequest $request): RedirectResponse
    {
        $graduate = Graduate::create($request->validated());

        $this->allocationService->allocateBaseTickets($graduate);

        return redirect()->route('graduates.index')
            ->with('success', "Graduate '{$graduate->student_name}' created and base tickets allocated successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Graduate $graduate): Response
    {
        $graduate->load([
            'user',
            'ceremony',
            'tickets' => fn ($query) => $query->latest(),
            'ticketRequests' => fn ($query) => $query->latest(),
        ]);

        return Inertia::render('Admin/Graduates/Show', [
            'graduate' => $graduate,
            'stats' => [
                'total_tickets' => $graduate->getTotalTicketsAttribute(),
                'attendance_rate' => $graduate->getAttendanceRateAttribute(),
                'request_status' => $graduate->getRequestStatusAttribute(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Graduate $graduate): Response
    {
        $ceremonies = Ceremony::query()->where('is_active', true)->get(['id', 'name']);
        $users = User::query()->role('Student')->get(['id', 'name', 'email']);

        return Inertia::render('Admin/Graduates/Edit', [
            'graduate' => $graduate,
            'ceremonies' => $ceremonies,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGraduateRequest $request, Graduate $graduate): RedirectResponse
    {
        $graduate->update($request->validated());

        return redirect()->route('graduates.show', $graduate)
            ->with('success', "Graduate '{$graduate->student_name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Graduate $graduate): RedirectResponse
    {
        $name = $graduate->student_name;
        $graduate->delete();

        return redirect()->route('graduates.index')
            ->with('success', "Graduate '{$name}' deleted successfully.");
    }

    /**
     * Show the import graduates form.
     */
    public function import(): Response
    {
        $ceremonies = Ceremony::query()->where('is_active', true)->get(['id', 'name']);

        return Inertia::render('Admin/Graduates/Import', [
            'ceremonies' => $ceremonies,
        ]);
    }

    /**
     * Process the import of graduates from CSV.
     */
    public function processImport(Request $request): RedirectResponse
    {
        $request->validate([
            'ceremony_id' => 'required|exists:ceremonies,id',
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $ceremony = Ceremony::findOrFail($request->ceremony_id);
        $file = $request->file('file');
        $data = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($data);

        $imported = 0;
        $errors = [];

        foreach ($data as $index => $row) {
            try {
                if (count($row) < 6) {
                    $errors[] = 'Row '.($index + 2).': Insufficient data';

                    continue;
                }

                $email = $row[3] ?? null;
                $user = User::where('email', $email)->first();

                if (! $user) {
                    $user = User::create([
                        'name' => $row[1],
                        'email' => $email,
                        'password' => bcrypt('password'),
                    ]);
                    $user->assignRole('Student');
                }

                $graduate = Graduate::create([
                    'user_id' => $user->id,
                    'ceremony_id' => $ceremony->id,
                    'student_id' => $row[0],
                    'student_name' => $row[1],
                    'degree_level' => $row[2],
                    'faculty' => $row[4],
                    'department' => $row[5],
                ]);

                $this->allocationService->allocateBaseTickets($graduate);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = 'Row '.($index + 2).': '.$e->getMessage();
            }
        }

        if (count($errors) > 0) {
            return redirect()->back()
                ->with('warning', "Imported {$imported} graduates with ".count($errors).' errors.')
                ->with('errors', $errors);
        }

        return redirect()->route('graduates.index')
            ->with('success', "Successfully imported {$imported} graduates.");
    }
}
