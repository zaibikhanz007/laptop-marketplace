<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8 pb-28">
        <div class="mb-6">
            <p class="text-sm text-gray-500">Saved for later</p>
            <h1 class="text-3xl font-bold text-gray-900">Favourites</h1>
        </div>
        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @forelse ($wishlists as $wishlist)
                @php $laptop = $wishlist->laptop; @endphp
                <article class="bg-white rounded-lg shadow overflow-hidden">
                    <a href="{{ route('laptops.show', $laptop) }}" class="block">
                        <div class="aspect-[4/3] bg-gray-100 relative">
                            <img
                                src="{{ $laptop->images->first() ? asset('storage/'.$laptop->images->first()->path) : asset('images/laptop-placeholder.svg') }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'"
                                alt="{{ $laptop->brand }} {{ $laptop->model }}"
                                class="w-full h-full object-cover"
                            >
                            @if ($laptop->status !== 'available' || $laptop->approval_status !== 'approved')
                                <span class="absolute inset-x-0 bottom-0 bg-gray-900/80 text-white text-center py-2 text-sm font-semibold">No longer available</span>
                            @endif
                            @if ($laptop->is_featured)
                                <span class="absolute top-2 left-2 bg-amber-400 text-amber-950 text-xs font-bold px-2 py-1 rounded">Featured</span>
                            @endif
                        </div>
                        <div class="p-4">
                            <h2 class="font-semibold">{{ $laptop->brand }} {{ $laptop->model }}</h2>
                            <p class="mt-1 text-sm text-gray-500">{{ $laptop->ram }} · {{ $laptop->storage }}</p>
                            <p class="mt-2 text-lg font-bold text-indigo-600">Rs. {{ number_format($laptop->price) }}</p>
                        </div>
                    </a>
                    <div class="px-4 pb-4 flex justify-between items-center">
                        @if ($laptop->status === 'available' && $laptop->approval_status === 'approved')
                            <span class="text-xs text-green-700">Available</span>
                        @else
                            <span class="text-xs text-gray-500">{{ ucfirst($laptop->status) }}</span>
                        @endif
                        <form method="POST" action="{{ route('wishlist.toggle', $laptop) }}">
                            @csrf
                            <button class="text-sm text-red-600 hover:text-red-800">Remove</button>
                        </form>
                    </div>
                </article>
            @empty
                <p class="col-span-full rounded-lg bg-white p-10 text-center text-gray-500">No favourites saved yet.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $wishlists->links() }}</div>
    </div>
</x-app-layout>
