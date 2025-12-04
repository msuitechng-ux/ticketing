<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCeremonyRequest;
use App\Http\Requests\UpdateCeremonyRequest;
use App\Models\Ceremony;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class CeremonyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $ceremonies = Ceremony::query()
            ->withCount(['graduates', 'tickets'])
            ->latest('ceremony_date')
            ->paginate(15);

        return Inertia::render('Admin/Ceremonies/Index', [
            'ceremonies' => $ceremonies,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Ceremonies/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCeremonyRequest $request): RedirectResponse
    {
        $ceremony = Ceremony::create($request->validated());

        return redirect()->route('ceremonies.index')
            ->with('success', "Ceremony '{$ceremony->name}' created successfully.");
    }

    /**
     * Display the specified resource.
     */
    public function show(Ceremony $ceremony): Response
    {
        $ceremony->load([
            'graduates' => fn ($query) => $query->with('user')->latest(),
            'tickets' => fn ($query) => $query->with('graduate')->latest(),
            'ticketRequests' => fn ($query) => $query->with('graduate')->latest(),
        ]);

        $ceremony->loadCount([
            'graduates',
            'tickets',
            'ticketRequests',
        ]);

        return Inertia::render('Admin/Ceremonies/Show', [
            'ceremony' => $ceremony,
            'stats' => [
                'total_tickets_allocated' => $ceremony->getTotalTicketsAllocatedAttribute(),
                'total_tickets_used' => $ceremony->getTotalTicketsUsedAttribute(),
                'utilization_rate' => $ceremony->getUtilizationRateAttribute(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ceremony $ceremony): Response
    {
        return Inertia::render('Admin/Ceremonies/Edit', [
            'ceremony' => $ceremony,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCeremonyRequest $request, Ceremony $ceremony): RedirectResponse
    {
        $ceremony->update($request->validated());

        return redirect()->route('ceremonies.show', $ceremony)
            ->with('success', "Ceremony '{$ceremony->name}' updated successfully.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ceremony $ceremony): RedirectResponse
    {
        $name = $ceremony->name;
        $ceremony->delete();

        return redirect()->route('ceremonies.index')
            ->with('success', "Ceremony '{$name}' deleted successfully.");
    }
}
