<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="mb-6">
            <p class="text-sm text-gray-500">Completed transaction</p>
            <h1 class="text-3xl font-bold text-gray-900">Review {{ $order->seller->name }}</h1>
            <p class="mt-2 text-gray-600">{{ $order->laptop->brand }} {{ $order->laptop->model }}</p>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('reviews.store', $order) }}" class="bg-white rounded-lg shadow p-6 space-y-6">
            @csrf

            <fieldset>
                <legend class="block text-sm font-semibold text-gray-800">Rating</legend>
                <div class="mt-3 flex-row-reverse justify-end inline-flex gap-1 star-rating" aria-label="Choose a star rating">
                    @for ($star = 5; $star >= 1; $star--)
                        <input class="sr-only peer" type="radio" id="rating-{{ $star }}" name="rating" value="{{ $star }}" @checked(old('rating') == $star) required>
                        <label for="rating-{{ $star }}" class="cursor-pointer text-3xl text-gray-300 peer-checked:text-yellow-400 hover:text-yellow-400" title="{{ $star }} star{{ $star === 1 ? '' : 's' }}">★</label>
                    @endfor
                </div>
                @error('rating')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </fieldset>

            <div>
                <label for="comment" class="block text-sm font-semibold text-gray-800">Comment <span class="font-normal text-gray-500">(optional)</span></label>
                <textarea id="comment" name="comment" rows="5" maxlength="1000" class="mt-2 w-full border-gray-300 rounded-md" placeholder="Share your experience with this seller">{{ old('comment') }}</textarea>
                @error('comment')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('orders.my') }}" class="px-4 py-2 text-gray-600 hover:text-gray-900">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Submit Review</button>
            </div>
        </form>

        <style>
            .star-rating input:checked ~ label { color: #fbbf24; }
            .star-rating label:hover,
            .star-rating label:hover ~ label { color: #fbbf24; }
        </style>
    </div>
</x-app-layout>
