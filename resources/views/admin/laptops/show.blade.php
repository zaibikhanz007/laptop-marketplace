<x-admin-layout>
    <div class="mb-6 flex items-center justify-between"><div><p class="text-sm text-gray-500">Listing moderation</p><h1 class="text-3xl font-bold">{{ $laptop->brand }} {{ $laptop->model }}</h1></div><a href="{{ route('admin.laptops.index') }}" class="text-sm text-indigo-600">Back</a></div>
    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white border rounded-lg p-5">
            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div><p class="text-gray-500">Seller</p><p class="font-medium">{{ $laptop->seller->name }}</p></div>
                <div><p class="text-gray-500">Price</p><p class="font-medium">Rs. {{ number_format($laptop->price) }}</p></div>
                <div><p class="text-gray-500">Processor</p><p>{{ $laptop->processor ?: '-' }}</p></div>
                <div><p class="text-gray-500">RAM / Storage</p><p>{{ $laptop->ram ?: '-' }} / {{ $laptop->storage ?: '-' }}</p></div>
                <div><p class="text-gray-500">Location</p><p>{{ $laptop->location }}</p></div>
                <div><p class="text-gray-500">Status</p><p>{{ ucfirst($laptop->status) }}</p></div>
            </div>
            @if($laptop->rejection_reason)<div class="mt-5 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong>Rejection reason:</strong> {{ $laptop->rejection_reason }}</div>@endif
            <div class="mt-5 grid grid-cols-2 sm:grid-cols-3 gap-3">
                @forelse($laptop->images as $image)<img src="{{ asset('storage/'.$image->path) }}" onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'" class="w-full h-32 object-cover rounded-md" alt="Laptop image">@empty<img src="{{ asset('images/laptop-placeholder.svg') }}" class="w-full h-48 object-contain rounded-md bg-gray-100" alt="Laptop illustration">@endforelse
            </div>
        </section>
        <section class="bg-white border rounded-lg p-5 space-y-3">
            <form method="POST" action="{{ route('admin.laptops.feature',$laptop) }}" class="space-y-2 border-b pb-4">
                @csrf @method('PATCH')
                <input type="hidden" name="is_featured" value="{{ $laptop->is_featured ? 0 : 1 }}">
                <label for="featured-until" class="block text-sm font-medium">Featured until (optional)</label>
                <input id="featured-until" type="date" name="featured_until" value="{{ $laptop->featured_until?->format('Y-m-d') }}" class="w-full border-gray-300 rounded-md">
                <button class="w-full px-3 py-2 {{ $laptop->is_featured ? 'bg-amber-600' : 'bg-indigo-600' }} text-white rounded-md">
                    {{ $laptop->is_featured ? 'Remove Feature' : 'Feature Listing' }}
                </button>
            </form>
            @if($laptop->status === 'pending')
                <form method="POST" action="{{ route('admin.laptops.approve',$laptop) }}">@csrf @method('PATCH')<button class="w-full px-3 py-2 bg-green-600 text-white rounded-md">Approve</button></form>
                <form method="POST" action="{{ route('admin.laptops.reject',$laptop) }}" class="space-y-2">
                    @csrf @method('PATCH')
                    <textarea name="rejection_reason" required minlength="3" maxlength="1000" class="w-full border-gray-300 rounded-md" placeholder="Rejection reason"></textarea>
                    @error('rejection_reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    <button class="w-full px-3 py-2 bg-red-600 text-white rounded-md">Reject</button>
                </form>
            @elseif($laptop->status === 'available')
                <form method="POST" action="{{ route('admin.laptops.block',$laptop) }}" onsubmit="return confirm('Block this listing?')">@csrf @method('PATCH')<button class="w-full px-3 py-2 bg-red-600 text-white rounded-md">Block Listing</button></form>
            @elseif($laptop->status === 'blocked')
                <form method="POST" action="{{ route('admin.laptops.unblock',$laptop) }}">@csrf @method('PATCH')<button class="w-full px-3 py-2 bg-green-600 text-white rounded-md">Unblock Listing</button></form>
            @endif
            <form method="POST" action="{{ route('admin.laptops.destroy',$laptop) }}" onsubmit="return confirm('Permanently delete this listing and all images?')">@csrf @method('DELETE')<button class="w-full px-3 py-2 bg-gray-900 text-white rounded-md">Delete Listing</button></form>
        </section>
    </div>
</x-admin-layout>
