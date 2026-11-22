@props(['laptop', 'wishlistedIds' => [], 'compareIds' => []])

@php
    $wishlisted = in_array($laptop->id, $wishlistedIds);
    $compared = in_array($laptop->id, $compareIds);
    $cover = $laptop->images->first();
@endphp

<article class="lapmarket-card relative overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lg">
    <a href="{{ route('laptops.show', $laptop) }}" class="block">
        <div class="aspect-[4/3] bg-gray-100 relative">
            <img
                src="{{ $cover ? asset('storage/'.$cover->path) : asset('images/laptop-placeholder.svg') }}"
                onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'"
                class="w-full h-full object-cover"
                alt="{{ $laptop->brand }} {{ $laptop->model }}"
            >
            @if ($laptop->is_featured && (!$laptop->featured_until || $laptop->featured_until->isToday() || $laptop->featured_until->isFuture()))
                <span class="absolute top-2 left-2 bg-amber-400 text-amber-950 text-xs font-bold px-2 py-1 rounded">Featured</span>
            @endif
        </div>
        <div class="p-4">
            <h3 class="font-semibold">{{ $laptop->brand }} {{ $laptop->model }}</h3>
            <p class="text-sm text-gray-500">{{ $laptop->ram }} · {{ $laptop->storage }} · {{ ucfirst(str_replace('_', ' ', $laptop->condition)) }}</p>
            <p class="mt-2 text-lg font-bold text-indigo-600">Rs. {{ number_format($laptop->price) }}</p>
            <p class="text-xs text-gray-400">{{ $laptop->location }}</p>
            @if ($laptop->seller)
                <div class="mt-2 flex flex-wrap items-center gap-2">
                    <x-verified-badge :verified="$laptop->seller->is_verified" />
                    <x-star-rating :rating="$laptop->seller->reviews_received_avg_rating ?? 0" :count="$laptop->seller->reviews_received_count" />
                </div>
            @endif
        </div>
    </a>
    <div class="px-4 pb-4 flex items-center justify-between gap-2">
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $laptop) }}">
                @csrf
                <button aria-label="{{ $wishlisted ? 'Remove from favourites' : 'Add to favourites' }}" class="p-2 rounded-full hover:bg-red-50 {{ $wishlisted ? 'text-red-600' : 'text-gray-500' }}">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1.1-1.1a5.5 5.5 0 0 0-7.8 7.8l1.1 1.1L12 21l7.8-7.5 1.1-1.1a5.5 5.5 0 0 0-.1-7.8Z"/>
                    </svg>
                </button>
            </form>
        @else
            <span></span>
        @endauth
        <form method="POST" action="{{ $compared ? route('compare.remove', $laptop) : route('compare.add', $laptop) }}">
            @csrf
            @if ($compared) @method('DELETE') @endif
            <button class="text-xs font-medium px-3 py-1.5 rounded-md {{ $compared ? 'bg-indigo-100 text-indigo-700' : 'border border-gray-300 text-gray-600 hover:border-indigo-400' }}">
                {{ $compared ? 'Added to compare' : 'Compare' }}
            </button>
        </form>
    </div>
</article>
