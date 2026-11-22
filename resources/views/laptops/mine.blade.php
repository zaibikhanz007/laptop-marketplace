<x-app-layout>
    <div class="max-w-5xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">My Laptops</h1>
            <a href="{{ route('laptops.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md">+ Sell a Laptop</a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($laptops as $laptop)
                <div class="p-4 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="w-20 h-16 bg-gray-100 rounded overflow-hidden">
                        <img
                            src="{{ $laptop->images->first() ? asset('storage/'.$laptop->images->first()->path) : asset('images/laptop-placeholder.svg') }}"
                            onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'"
                            alt="{{ $laptop->brand }} {{ $laptop->model }}"
                            class="w-full h-full object-cover"
                        >
                    </div>
                    <div class="flex-1 min-w-0 break-words">
                        <p class="font-semibold">{{ $laptop->brand }} {{ $laptop->model }}</p>
                        <p class="text-sm text-gray-500">Rs. {{ number_format($laptop->price) }}</p>
                        <div class="mt-1 flex gap-2 text-xs">
                            @php
                                $statusClasses = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'available' => 'bg-green-100 text-green-800',
                                    'sold' => 'bg-gray-200 text-gray-700',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'blocked' => 'bg-red-100 text-red-800',
                                ];
                            @endphp
                            <span class="px-2 py-1 rounded {{ $statusClasses[$laptop->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ ucfirst($laptop->status) }}
                            </span>
                        </div>
                    </div>
                    <a href="{{ route('laptops.edit', $laptop) }}" class="text-indigo-600">Edit</a>
                    <form method="POST" action="{{ route('laptops.destroy', $laptop) }}"
                          onsubmit="return confirm('Delete this laptop?')">
                        @csrf @method('DELETE')
                        <button class="text-red-600">Delete</button>
                    </form>
                </div>
            @empty
                <p class="p-6 text-gray-500">Abhi tak koi laptop list nahi kiya.</p>
            @endforelse
        </div>

        <div class="mt-6">{{ $laptops->links() }}</div>
    </div>
</x-app-layout>
