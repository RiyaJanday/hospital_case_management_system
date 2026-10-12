<?php

namespace App\Http\Requests;

use App\Models\MedicalCase;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicalCaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        // When editing, the URL contains the case. We ignore that case in the unique check.
        // On the web the route value is a MedicalCase object, in the API it is a plain id.
        $routeValue = $this->route('case');
        if ($routeValue === null) {
            $routeValue = $this->route('id'); // the API routes use {id}
        }
        if ($routeValue instanceof MedicalCase) {
            $currentCaseId = $routeValue->id;
        } else {
            $currentCaseId = $routeValue; // null when creating
        }

        return [
            'case_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cases', 'case_number')->ignore($currentCaseId),
            ],
            'patient_reference' => ['required', 'string', 'max:100'],
            'surgeon_name' => ['required', 'string', 'max:100'],
            'implant_type' => ['required', 'string', 'max:100'],
            'status' => ['required', Rule::in(MedicalCase::STATUSES)],
            'priority' => ['required', Rule::in(MedicalCase::PRIORITIES)],
            'surgery_date' => ['nullable', 'date'],
        ];
    }
}
