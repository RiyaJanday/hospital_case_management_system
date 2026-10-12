<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Case {{ $case->case_number }}</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <form method="POST" action="{{ route('cases.update', $case) }}">
                    @csrf
                    @method('PUT')

                    @include('cases._fields')

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md">Update Case</button>
                    <a href="{{ route('cases.show', $case) }}" class="ml-2 text-gray-600">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
