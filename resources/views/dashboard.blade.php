<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Total cases</p>
                    <p class="text-3xl font-bold">{{ $totalCases }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Planning</p>
                    <p class="text-3xl font-bold">{{ $planningCases }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">In Review</p>
                    <p class="text-3xl font-bold">{{ $inReviewCases }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Approved</p>
                    <p class="text-3xl font-bold">{{ $approvedCases }}</p>
                </div>
                <div class="bg-white shadow-sm rounded-lg p-4 text-center">
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-3xl font-bold">{{ $completedCases }}</p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
