<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <header class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-sm text-gray-500">Seller profile</p>
                    <h1 class="flex flex-wrap items-center gap-2 text-3xl font-bold text-gray-900">
                        {{ $seller->name }}
                        <x-verified-badge :verified="$seller->is_verified" />
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ $seller->city ?: 'Location not provided' }} · Member since {{ $seller->created_at->format('M Y') }}
                    </p>
                </div>
                <div class="text-left sm:text-right">
                    <x-star-rating :rating="$seller->reviews_received_avg_rating ?? 0" :count="$seller->reviews_received_count" />
                    @auth
                        @if (auth()->id() !== $seller->id)
                            <div class="mt-2">
                                <a href="{{ route('complaints.create', $seller) }}" class="text-sm font-medium text-red-600 hover:text-red-800">Report seller</a>
                            </div>
                        @endif
                    @endauth
                </div>
            </div>
        </header>

        <section class="mt-8">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Available laptops</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse ($laptops as $laptop)
                    <x-laptop-card :laptop="$laptop" :wishlisted-ids="$wishlistedIds" :compare-ids="$compareIds" />
                @empty
                    <p class="text-gray-500">This seller has no active listings.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $laptops->withQueryString()->links() }}</div>
        </section>

        <section class="mt-10">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Latest reviews</h2>
            <div class="bg-white rounded-lg shadow divide-y">
                @forelse ($reviews as $review)
                    <article class="p-5">
                        <div class="flex items-center justify-between gap-3">
                            <p class="font-semibold text-gray-900">{{ $review->reviewer->name }}</p>
                            <x-star-rating :rating="$review->rating" />
                        </div>
                        <p class="mt-2 text-gray-700">{{ $review->comment ?: 'No written comment.' }}</p>
                        <p class="mt-2 text-xs text-gray-400">{{ $review->created_at->format('d M Y') }}</p>
                    </article>
                @empty
                    <p class="p-6 text-gray-500">No reviews yet.</p>
                @endforelse
            </div>
            <div class="mt-5">{{ $reviews->withQueryString()->links() }}</div>
        </section>
    </div>
</x-app-layout>
