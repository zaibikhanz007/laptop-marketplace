<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="mb-6">
            <p class="text-sm text-gray-500">Trust & safety</p>
            <h1 class="text-3xl font-bold text-gray-900">My Complaints</h1>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
            @forelse ($complaints as $complaint)
                @php
                    $badge = match ($complaint->status) {
                        'open' => 'bg-yellow-100 text-yellow-800',
                        'warned' => 'bg-orange-100 text-orange-800',
                        'blocked' => 'bg-red-100 text-red-800',
                        'dismissed' => 'bg-gray-100 text-gray-700',
                        default => 'bg-gray-100 text-gray-700',
                    };
                @endphp
                <article class="bg-white rounded-lg shadow p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="font-semibold text-gray-900">{{ $complaint->subject }}</h2>
                            <p class="mt-1 text-sm text-gray-500">Against {{ $complaint->against->name }} · {{ $complaint->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                            {{ ucfirst($complaint->status) }}
                        </span>
                    </div>
                    <p class="mt-3 text-gray-700">{{ $complaint->description }}</p>
                </article>
            @empty
                <div class="bg-white rounded-lg shadow p-10 text-center text-gray-500">
                    You have not submitted any complaints.
                </div>
            @endforelse
        </div>
        <div class="mt-6">{{ $complaints->links() }}</div>
    </div>
</x-app-layout>
