{{-- Form fields shared by the create and edit pages. --}}
{{-- $case is a MedicalCase (empty on create, filled on edit). old() keeps typed values after an error. --}}

<div class="mb-4">
    <label for="case_number" class="block font-medium text-sm text-gray-700">Case Number</label>
    <input type="text" id="case_number" name="case_number" placeholder="CASE-0001"
           value="{{ old('case_number', $case->case_number) }}"
           class="w-full border-gray-300 rounded-md shadow-sm">
    @error('case_number')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="patient_reference" class="block font-medium text-sm text-gray-700">Patient Reference (dummy code)</label>
    <input type="text" id="patient_reference" name="patient_reference" placeholder="PAT-00001"
           value="{{ old('patient_reference', $case->patient_reference) }}"
           class="w-full border-gray-300 rounded-md shadow-sm">
    @error('patient_reference')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="surgeon_name" class="block font-medium text-sm text-gray-700">Surgeon Name</label>
    <input type="text" id="surgeon_name" name="surgeon_name"
           value="{{ old('surgeon_name', $case->surgeon_name) }}"
           class="w-full border-gray-300 rounded-md shadow-sm">
    @error('surgeon_name')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="implant_type" class="block font-medium text-sm text-gray-700">Implant Type</label>
    <input type="text" id="implant_type" name="implant_type"
           value="{{ old('implant_type', $case->implant_type) }}"
           class="w-full border-gray-300 rounded-md shadow-sm">
    @error('implant_type')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="status" class="block font-medium text-sm text-gray-700">Status</label>
    <select id="status" name="status" class="w-full border-gray-300 rounded-md shadow-sm">
        <option value="">-- Select status --</option>
        @foreach (\App\Models\MedicalCase::STATUSES as $statusOption)
            <option value="{{ $statusOption }}" @selected(old('status', $case->status) === $statusOption)>
                {{ $statusOption }}
            </option>
        @endforeach
    </select>
    @error('status')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="priority" class="block font-medium text-sm text-gray-700">Priority</label>
    <select id="priority" name="priority" class="w-full border-gray-300 rounded-md shadow-sm">
        <option value="">-- Select priority --</option>
        @foreach (\App\Models\MedicalCase::PRIORITIES as $priorityOption)
            <option value="{{ $priorityOption }}" @selected(old('priority', $case->priority) === $priorityOption)>
                {{ $priorityOption }}
            </option>
        @endforeach
    </select>
    @error('priority')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="surgery_date" class="block font-medium text-sm text-gray-700">Surgery Date (optional)</label>
    <input type="date" id="surgery_date" name="surgery_date"
           value="{{ old('surgery_date', $case->surgery_date ? $case->surgery_date->format('Y-m-d') : '') }}"
           class="w-full border-gray-300 rounded-md shadow-sm">
    @error('surgery_date')
        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
    @enderror
</div>
