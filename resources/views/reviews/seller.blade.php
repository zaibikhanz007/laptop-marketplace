<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-6">My Reviews</h1>
        <div class="bg-white rounded-lg shadow divide-y">
            @forelse ($reviews as $review)
                <article class="p-5">
                    <div class="flex items-center justify-between">
                        <p class="font-semibold">{{ $review->reviewer->name }}</p>
                        <p class="text-yellow-500">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                    </div>
                    <p class="mt-2 text-gray-700">{{ $review->comment ?: 'No written comment.' }}</p>
                    <p class="mt-2 text-xs text-gray-400">{{ $review->created_at->format('d M Y') }}</p>
                </article>
            @empty
                <p class="p-6 text-gray-500">No reviews yet.</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $reviews->links() }}</div>
    </div>
</x-app-layout>
