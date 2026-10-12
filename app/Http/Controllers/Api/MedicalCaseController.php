<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MedicalCaseRequest;
use App\Models\MedicalCase;
use Illuminate\Support\Facades\Gate;

class MedicalCaseController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', MedicalCase::class);

        $cases = MedicalCase::orderBy('id', 'desc')->paginate(10);

        return $this->respond(true, $cases, 'Cases retrieved.', 200);
    }

    public function store(MedicalCaseRequest $request)
    {
        Gate::authorize('create', MedicalCase::class);

        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $case = MedicalCase::create($data);

        return $this->respond(true, $case, 'Case created.', 201);
    }

    public function show($id)
    {
        $case = MedicalCase::find($id);
        if (!$case) {
            return $this->respond(false, null, 'Case not found.', 404);
        }

        Gate::authorize('view', $case);

        return $this->respond(true, $case, 'Case retrieved.', 200);
    }

    public function update(MedicalCaseRequest $request, $id)
    {
        $case = MedicalCase::find($id);
        if (!$case) {
            return $this->respond(false, null, 'Case not found.', 404);
        }

        Gate::authorize('update', $case);

        $case->update($request->validated());

        return $this->respond(true, $case, 'Case updated.', 200);
    }

    public function destroy($id)
    {
        $case = MedicalCase::find($id);
        if (!$case) {
            return $this->respond(false, null, 'Case not found.', 404);
        }

        Gate::authorize('delete', $case);

        $case->delete();

        return $this->respond(true, null, 'Case deleted.', 200);
    }

    private function respond($success, $data, $message, $statusCode)
    {
        return response()->json([
            'success' => $success,
            'data' => $data,
            'message' => $message,
        ], $statusCode);
    }
}
