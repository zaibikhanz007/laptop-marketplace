<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-8 pb-32">
        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        <div class="grid lg:grid-cols-2 gap-8">
            <section>
                @if ($laptop->images->isNotEmpty())
                    <div class="gallery-main overflow-hidden rounded-xl bg-gray-100">
                        @foreach ($laptop->images as $image)
                            <input class="gallery-selector sr-only" type="radio" name="laptop-gallery" id="gallery-{{ $image->id }}" @checked($loop->first)>
                            <div class="gallery-frame aspect-[4/3]">
                                <img src="{{ $image->url }}" onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'" alt="{{ $laptop->brand }} {{ $laptop->model }} image {{ $loop->iteration }}" class="w-full h-full object-contain">
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 flex gap-3 overflow-x-auto pb-2">
                        @foreach ($laptop->images as $image)
                            <label for="gallery-{{ $image->id }}" class="cursor-pointer shrink-0 rounded-md overflow-hidden border-2 border-transparent has-[:checked]:border-indigo-600">
                                <img src="{{ $image->url }}" onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'" alt="View image {{ $loop->iteration }}" class="w-20 h-16 object-cover">
                            </label>
                        @endforeach
                    </div>
                    <style>
                        .gallery-frame { display: none; }
                        .gallery-selector:checked + .gallery-frame { display: block; }
                    </style>
                @else
                    <div class="aspect-[4/3] overflow-hidden rounded-xl bg-gray-100">
                        <img src="{{ asset('images/laptop-placeholder.svg') }}" alt="{{ $laptop->brand }} {{ $laptop->model }}" class="w-full h-full object-contain">
                    </div>
                @endif
            </section>

            <section class="space-y-5">
                <div>
                    <div class="flex flex-wrap gap-2 mb-2">
                        @if ($laptop->is_featured && (!$laptop->featured_until || $laptop->featured_until->isToday() || $laptop->featured_until->isFuture()))
                            <span class="bg-amber-100 text-amber-800 text-xs font-bold px-2.5 py-1 rounded-full">Featured</span>
                        @endif
                        <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-1 rounded-full">Available</span>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $laptop->title ?: $laptop->brand . ' ' . $laptop->model }}</h1>
                    <p class="mt-2 text-3xl font-bold text-indigo-600">Rs. {{ number_format($laptop->price) }}</p>
                </div>

                <dl class="grid grid-cols-2 gap-3 text-sm bg-white p-4 rounded-lg border">
                    <dt class="text-gray-500">Title</dt><dd>{{ $laptop->title ?: $laptop->brand . ' ' . $laptop->model }}</dd>
                    <dt class="text-gray-500">Processor</dt><dd>{{ $laptop->processor ?: '-' }}{{ $laptop->generation ? ' · ' . $laptop->generation : '' }}</dd>
                    <dt class="text-gray-500">RAM</dt><dd>{{ $laptop->ram ?: '-' }}</dd>
                    <dt class="text-gray-500">Storage</dt><dd>{{ $laptop->storage_capacity ? $laptop->storage_capacity . ' ' . $laptop->storage_type : ($laptop->storage ?: '-') }}</dd>
                    <dt class="text-gray-500">Graphics</dt><dd>{{ $laptop->graphics_card ?: '-' }}</dd>
                    <dt class="text-gray-500">Screen size</dt><dd>{{ $laptop->screen_size ?: '-' }}</dd>
                    <dt class="text-gray-500">Condition</dt><dd>{{ ucfirst(str_replace('_', ' ', $laptop->condition)) }}</dd>
                    <dt class="text-gray-500">Location</dt><dd>{{ $laptop->location }}</dd>
                    <dt class="text-gray-500">Seller</dt>
                    <dd>
                        <a href="{{ route('sellers.show', $laptop->seller) }}" class="inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-800">
                            {{ $laptop->seller->name }}
                        </a>
                        <x-verified-badge :verified="$laptop->seller->is_verified" class="ml-1" />
                        <div class="mt-1"><x-star-rating :rating="$laptop->seller->reviews_received_avg_rating ?? 0" :count="$laptop->seller->reviews_received_count" /></div>
                    </dd>
                </dl>
                @if ($laptop->description)<p class="text-gray-700 whitespace-pre-line">{{ $laptop->description }}</p>@endif

                <div class="flex flex-wrap items-center gap-3">
                    @auth
                        @if ($isWishlisted || $laptop->status === 'available')
                            <form method="POST" action="{{ route('wishlist.toggle', $laptop) }}">
                                @csrf
                                <button class="inline-flex items-center gap-2 px-4 py-2 border rounded-md {{ $isWishlisted ? 'text-red-600 border-red-200 bg-red-50' : 'text-gray-700 hover:bg-gray-50' }}">
                                    <span aria-hidden="true">{{ $isWishlisted ? '♥' : '♡' }}</span>{{ $isWishlisted ? 'Saved to favourites' : 'Add to favourites' }}
                                </button>
                            </form>
                        @endif
                        @if ($laptop->user_id !== auth()->id())
                            <a href="{{ route('complaints.create', $laptop->seller) }}" class="text-sm text-red-600 hover:text-red-800">Report seller</a>
                        @endif
                    @endauth
                    @if (in_array($laptop->id, $compareIds))
                        <form method="POST" action="{{ route('compare.remove', $laptop) }}">@csrf @method('DELETE')<button class="text-sm text-indigo-600">Remove from compare</button></form>
                    @else
                        <form method="POST" action="{{ route('compare.add', $laptop) }}">@csrf<button class="text-sm text-indigo-600">Add to compare</button></form>
                    @endif
                </div>

                @auth
                    @if ($laptop->user_id === auth()->id())
                        <div class="rounded-lg border bg-gray-50 px-4 py-3 text-sm text-gray-600">This is your listing.</div>
                    @elseif ($existingOrder)
                        @php $orderBadge = ['pending'=>'bg-yellow-100 text-yellow-800','accepted'=>'bg-blue-100 text-blue-800','rejected'=>'bg-red-100 text-red-800','cancelled'=>'bg-gray-100 text-gray-700','completed'=>'bg-green-100 text-green-800'][$existingOrder->status] ?? 'bg-gray-100 text-gray-700'; @endphp
                        <div class="border rounded-lg p-4 bg-white">
                            <div class="flex items-center justify-between gap-3">
                                <p class="font-semibold">Your purchase request</p>
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $orderBadge }}">{{ ucfirst($existingOrder->status) }}</span>
                            </div>
                            @if (in_array($existingOrder->status, ['accepted', 'completed'], true))
                                <div class="mt-3 rounded-md bg-green-50 p-3 text-sm">
                                    <p class="font-medium text-green-900">Seller contact</p>
                                    <p class="text-green-800">Phone: {{ $laptop->contact_number ?: ($laptop->seller->phone ?: 'Not provided') }}</p>
                                    <p class="text-green-800">City: {{ $laptop->seller->city ?: 'Not provided' }}</p>
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-500">Seller contact will be visible after acceptance.</p>
                            @endif
                            @if (in_array($existingOrder->status, ['pending', 'accepted'], true))
                                <form method="POST" action="{{ route('orders.cancel', $existingOrder) }}" class="mt-3">@csrf @method('PATCH')<button class="text-sm font-medium text-red-600">Cancel request</button></form>
                            @endif
                        </div>
                    @elseif ($laptop->status === 'available')
                        <form method="POST" action="{{ route('orders.store', $laptop) }}" class="w-full rounded-lg border bg-white p-4 space-y-3">
                            @csrf
                            <h2 class="font-semibold text-gray-900">Request Purchase</h2>
                            <div><label for="phone" class="block text-sm font-medium">Phone</label><input id="phone" type="tel" name="phone" value="{{ old('phone', auth()->user()->phone) }}" required maxlength="20" class="mt-1 w-full border-gray-300 rounded-md">@error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                            <div><label for="address" class="block text-sm font-medium">Address</label><textarea id="address" name="address" rows="3" required maxlength="1000" class="mt-1 w-full border-gray-300 rounded-md">{{ old('address', auth()->user()->address) }}</textarea>@error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                            <div><label for="payment_method" class="block text-sm font-medium">Payment method</label><select id="payment_method" name="payment_method" class="mt-1 w-full border-gray-300 rounded-md"><option value="cod">Cash on delivery</option><option value="bank_transfer">Bank transfer</option></select></div>
                            <button class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Send Purchase Request</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="inline-block bg-indigo-600 text-white px-4 py-2 rounded-md">Login to send a purchase request</a>
                @endauth
            </section>
        </div>
    </div>
</x-app-layout>




