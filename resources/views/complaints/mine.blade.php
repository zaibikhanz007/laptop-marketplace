<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">My Complaints</h1>
            <a href="{{ route('complaints.create') }}" class="bg-red-600 text-white px-4 py-2 rounded-md">New Report</a>
        </div>
        @if (session('success'))<div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>@endif
        <div class="space-y-3">
            @forelse ($complaints as $complaint)
                <article class="bg-white p-5 rounded-lg shadow">
                    <div class="flex justify-between">
                        <h2 class="font-semibold">{{ $complaint->subject }}</h2>
                        <span class="text-sm px-2 py-1 rounded bg-gray-100">{{ ucfirst($complaint->status) }}</span>
                    </div>
                    <p class="mt-2 text-gray-600">{{ $complaint->description }}</p>
                    <p class="mt-2 text-xs text-gray-400">Against: {{ $complaint->against->name }} · {{ $complaint->created_at->format('d M Y') }}</p>
                </article>
            @empty
                <p class="text-gray-500">No complaints submitted.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $complaints->links() }}</div>
    </div>
</x-app-layout>
