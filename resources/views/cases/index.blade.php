<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Cases</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                @if (session('success'))
                    <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
                @endif

                <form method="GET" action="{{ route('cases.index') }}" class="flex flex-col sm:flex-row gap-2 mb-4">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Case number or surgeon"
                           class="border-gray-300 rounded-md shadow-sm w-full sm:w-64">

                    <select name="status" class="border-gray-300 rounded-md shadow-sm">
                        <option value="">All statuses</option>
                        @foreach (\App\Models\MedicalCase::STATUSES as $statusOption)
                            <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>

                    <button type="submit" class="px-4 py-2 bg-gray-800 text-white rounded-md">Search</button>
                    <a href="{{ route('cases.index') }}" class="px-4 py-2 border rounded-md text-center">Reset</a>
                </form>

                @can('create', \App\Models\MedicalCase::class)
                    <a href="{{ route('cases.create') }}"
                       class="inline-block mb-4 px-4 py-2 bg-indigo-600 text-white rounded-md">+ New Case</a>
                @endcan

                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                            <tr class="border-b font-semibold">
                                <th class="py-2 pr-4">Case No.</th>
                                <th class="py-2 pr-4">Patient Ref</th>
                                <th class="py-2 pr-4">Surgeon</th>
                                <th class="py-2 pr-4">Implant</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Priority</th>
                                <th class="py-2 pr-4">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cases as $case)
                                <tr class="border-b">
                                    <td class="py-2 pr-4">{{ $case->case_number }}</td>
                                    <td class="py-2 pr-4">{{ $case->patient_reference }}</td>
                                    <td class="py-2 pr-4">{{ $case->surgeon_name }}</td>
                                    <td class="py-2 pr-4">{{ $case->implant_type }}</td>
                                    <td class="py-2 pr-4">{{ $case->status }}</td>
                                    <td class="py-2 pr-4">{{ $case->priority }}</td>
                                    <td class="py-2 pr-4 whitespace-nowrap">
                                        <a href="{{ route('cases.show', $case) }}" class="text-indigo-600">View</a>

                                        @can('update', $case)
                                            <a href="{{ route('cases.edit', $case) }}" class="text-green-700 ml-2">Edit</a>
                                        @endcan

                                        @can('delete', $case)
                                            <form method="POST" action="{{ route('cases.destroy', $case) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Delete this case?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 ml-2">Delete</button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-4 text-gray-500">No cases found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $cases->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
