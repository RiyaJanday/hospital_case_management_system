<?php

namespace App\Http\Controllers;

use App\Http\Requests\MedicalCaseRequest;
use App\Models\MedicalCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MedicalCaseController extends Controller
{
    // Case list with search, status filter and pagination (10 per page).
    public function index(Request $request)
    {
        Gate::authorize('viewAny', MedicalCase::class);

        $query = MedicalCase::query();

        // Search by case number OR surgeon name.
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('case_number', 'like', '%' . $search . '%')
                    ->orWhere('surgeon_name', 'like', '%' . $search . '%');
            });
        }

        // Filter by status.
        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        // withQueryString keeps the search/filter when moving between pages.
        $cases = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('cases.index', ['cases' => $cases]);
    }

    public function create()
    {
        Gate::authorize('create', MedicalCase::class);

        // An empty case so the shared form fields have something to read from.
        return view('cases.create', ['case' => new MedicalCase()]);
    }

    public function store(MedicalCaseRequest $request)
    {
        Gate::authorize('create', MedicalCase::class);

        // validated() returns only the fields that passed validation.
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        MedicalCase::create($data);

        return redirect()->route('cases.index')->with('success', 'Case created successfully.');
    }

    public function show(MedicalCase $case)
    {
        Gate::authorize('view', $case);

        return view('cases.show', ['case' => $case]);
    }

    public function edit(MedicalCase $case)
    {
        Gate::authorize('update', $case);

        return view('cases.edit', ['case' => $case]);
    }

    public function update(MedicalCaseRequest $request, MedicalCase $case)
    {
        Gate::authorize('update', $case);

        $case->update($request->validated());

        return redirect()->route('cases.show', $case)->with('success', 'Case updated successfully.');
    }

    public function destroy(MedicalCase $case)
    {
        // The server blocks Viewers here even if they send the request by hand.
        Gate::authorize('delete', $case);

        $case->delete();

        return redirect()->route('cases.index')->with('success', 'Case deleted.');
    }
}
