<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Case {{ $case->case_number }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif

                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-y-3 text-sm">
                    <dt class="font-semibold">ID</dt>
                    <dd class="sm:col-span-2">{{ $case->id }}</dd>

                    <dt class="font-semibold">Case Number</dt>
                    <dd class="sm:col-span-2">{{ $case->case_number }}</dd>

                    <dt class="font-semibold">Patient Reference</dt>
                    <dd class="sm:col-span-2">{{ $case->patient_reference }}</dd>

                    <dt class="font-semibold">Surgeon Name</dt>
                    <dd class="sm:col-span-2">{{ $case->surgeon_name }}</dd>

                    <dt class="font-semibold">Implant Type</dt>
                    <dd class="sm:col-span-2">{{ $case->implant_type }}</dd>

                    <dt class="font-semibold">Status</dt>
                    <dd class="sm:col-span-2">{{ $case->status }}</dd>

                    <dt class="font-semibold">Priority</dt>
                    <dd class="sm:col-span-2">{{ $case->priority }}</dd>

                    <dt class="font-semibold">Surgery Date</dt>
                    <dd class="sm:col-span-2">
                        {{ $case->surgery_date ? $case->surgery_date->format('d M Y') : 'Not set' }}
                    </dd>

                    <dt class="font-semibold">Created By</dt>
                    <dd class="sm:col-span-2">{{ $case->creator ? $case->creator->name : 'Unknown' }}</dd>

                    <dt class="font-semibold">Created At</dt>
                    <dd class="sm:col-span-2">{{ $case->created_at->format('d M Y, H:i') }}</dd>

                    <dt class="font-semibold">Updated At</dt>
                    <dd class="sm:col-span-2">{{ $case->updated_at->format('d M Y, H:i') }}</dd>
                </dl>

                <div class="mt-6">
                    <a href="{{ route('cases.index') }}" class="text-gray-600">&larr; Back to list</a>

                    @can('update', $case)
                        <a href="{{ route('cases.edit', $case) }}" class="ml-4 text-green-700">Edit</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
