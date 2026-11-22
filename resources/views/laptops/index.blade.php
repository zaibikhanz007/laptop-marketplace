<x-app-layout>
    <section class="lapmarket-hero">
        <div class="lapmarket-hero-inner">
            <span class="lapmarket-eyebrow">The marketplace</span>
            <h1>Find your next <span>laptop</span></h1>
        </div>
    </section>

    <div class="lapmarket-dock-wrap">
        <form method="GET" action="{{ route('laptops.index') }}" class="lapmarket-dock">
            <div class="lapmarket-field">
                <label for="q">Search</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Brand / model...">
            </div>
            <div class="lapmarket-field">
                <label for="brand">Brand</label>
                <select id="brand" name="brand" class="lapmarket-native-select"><option value="">All brands</option>@foreach ($brands as $brand)<option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>@endforeach</select>
            </div>
            <div class="lapmarket-field">
                <label for="condition">Condition</label>
                <select id="condition" name="condition" class="lapmarket-native-select"><option value="">Any condition</option>@foreach (['like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair', 'needs_repair' => 'Needs Repair'] as $key => $label)<option value="{{ $key }}" @selected(request('condition') === $key)>{{ $label }}</option>@endforeach</select>
            </div>
            <div class="lapmarket-field">
                <label for="city">Location</label>
                <select id="city" name="city" class="lapmarket-native-select"><option value="">All cities</option>@foreach ($cities as $city)<option value="{{ $city }}" @selected(request('city') === $city)>{{ $city }}</option>@endforeach</select>
            </div>
            <div class="lapmarket-field"><label for="min_price">Min price</label><input id="min_price" type="number" min="0" name="min_price" value="{{ request('min_price') }}" placeholder="Rs. 0"></div>
            <div class="lapmarket-field"><label for="max_price">Max price</label><input id="max_price" type="number" min="0" name="max_price" value="{{ request('max_price') }}" placeholder="Rs. 0"></div>
            <div class="lapmarket-field"><label for="sort">Sort</label><select id="sort" name="sort" class="lapmarket-native-select"><option value="" @selected(!request()->filled('sort'))>Featured first</option><option value="newest" @selected(request('sort') === 'newest')>Newest</option><option value="price_low" @selected(request('sort') === 'price_low')>Price: Low</option><option value="price_high" @selected(request('sort') === 'price_high')>Price: High</option></select></div>
            <button class="lapmarket-search-button" type="submit"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 4.5 4.5"></path></svg><span>Search laptops</span></button>
        </form>
    </div>

    <main class="lapmarket-results">
        @if (session('success'))<div class="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-800">{{ session('success') }}</div>@endif

        @if (request()->routeIs('home') && $featuredLaptops->isNotEmpty())
            <section class="mb-10">
                <div class="lapmarket-results-heading"><div><p class="text-sm text-emerald-600 font-semibold">Handpicked for you</p><h2>Featured laptops</h2></div><span>Popular listings</span></div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">@foreach ($featuredLaptops as $laptop)<x-laptop-card :laptop="$laptop" :wishlisted-ids="$wishlistedIds" :compare-ids="$compareIds" />@endforeach</div>
            </section>
        @endif

        <div class="lapmarket-results-heading"><h2>Available laptops</h2><span>{{ $laptops->total() }} results</span></div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @forelse ($laptops as $laptop)<x-laptop-card :laptop="$laptop" :wishlisted-ids="$wishlistedIds" :compare-ids="$compareIds" />@empty
                <p class="col-span-full rounded-2xl bg-white p-10 text-center text-slate-500">No laptops found. Try adjusting your filters.</p>
            @endforelse
        </div>
        <div class="mt-8">{{ $laptops->links() }}</div>
    </main>
</x-app-layout>
