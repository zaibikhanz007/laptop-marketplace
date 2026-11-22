@php $l = $laptop ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="brand" value="Brand" />
        <x-text-input id="brand" name="brand" class="w-full" :value="old('brand', $l?->brand)" required />
        <x-input-error :messages="$errors->get('brand')" />
    </div>
    <div>
        <x-input-label for="model" value="Model" />
        <x-text-input id="model" name="model" class="w-full" :value="old('model', $l?->model)" required />
        <x-input-error :messages="$errors->get('model')" />
    </div>
    <div>
        <x-input-label for="processor" value="Processor" />
        <x-text-input id="processor" name="processor" class="w-full" :value="old('processor', $l?->processor)" />
    </div>
    <div>
        <x-input-label for="ram" value="RAM (e.g. 8GB)" />
        <x-text-input id="ram" name="ram" class="w-full" :value="old('ram', $l?->ram)" />
    </div>
    <div>
        <x-input-label for="storage" value="Storage (e.g. 256GB SSD)" />
        <x-text-input id="storage" name="storage" class="w-full" :value="old('storage', $l?->storage)" />
    </div>
    <div>
        <x-input-label for="condition" value="Condition" />
        <select id="condition" name="condition" class="w-full border-gray-300 rounded-md" required>
            @foreach (['like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair', 'needs_repair' => 'Needs Repair'] as $k => $v)
                <option value="{{ $k }}" @selected(old('condition', $l?->condition) === $k)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label for="price" value="Price (Rs.)" />
        <x-text-input id="price" type="number" name="price" min="1" max="2000000000" class="w-full" :value="old('price', $l?->price)" required />
        <x-input-error :messages="$errors->get('price')" />
    </div>
    <div>
        <x-input-label for="location" value="Location" />
        <x-text-input id="location" name="location" class="w-full" :value="old('location', $l?->location)" required />
    </div>
    <div class="md:col-span-2">
        <p class="text-sm font-medium text-gray-700">Bank transfer payment details</p>
        <p class="mt-1 text-sm text-gray-500">Shown to the buyer only after you accept their request.</p>
    </div>
    <div>
        <x-input-label for="payment_account_title" value="Account Title" />
        <x-text-input id="payment_account_title" name="payment_account_title" class="w-full" :value="old('payment_account_title', $l?->payment_account_title)" />
        <x-input-error :messages="$errors->get('payment_account_title')" />
    </div>
    <div>
        <x-input-label for="payment_account_number" value="Account Number / IBAN" />
        <x-text-input id="payment_account_number" name="payment_account_number" class="w-full" :value="old('payment_account_number', $l?->payment_account_number)" />
        <x-input-error :messages="$errors->get('payment_account_number')" />
    </div>
    <div>
        <x-input-label for="payment_bank_name" value="Bank Name" />
        <x-text-input id="payment_bank_name" name="payment_bank_name" class="w-full" :value="old('payment_bank_name', $l?->payment_bank_name)" />
        <x-input-error :messages="$errors->get('payment_bank_name')" />
    </div>
    @if ($l && in_array($l->status, ['available', 'sold'], true))
        <div>
            <x-input-label for="status" value="Listing Status" />
            <select id="status" name="status" class="w-full border-gray-300 rounded-md" required>
                <option value="available" @selected(old('status', $l->status) === 'available')>Available</option>
                <option value="sold" @selected(old('status', $l->status) === 'sold')>Sold</option>
            </select>
        </div>
    @endif
    <div class="md:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="4" maxlength="5000"
                  class="w-full border-gray-300 rounded-md">{{ old('description', $l?->description) }}</textarea>
    </div>
    <div class="md:col-span-2">
        <x-input-label for="images" value="Images (max 6, 2MB each)" />
        <input id="images" type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp" class="w-full">
        <x-input-error :messages="$errors->get('images.*')" />
    </div>
</div>

@if ($l && $l->images->count())
    <div class="flex flex-wrap gap-3 mt-4">
        @foreach ($l->images as $img)
            <div class="relative">
                <img src="{{ asset('storage/' . $img->path) }}" onerror="this.onerror=null;this.src='{{ asset('images/laptop-placeholder.svg') }}'" class="w-24 h-24 object-cover rounded" alt="Laptop image">
                <button type="submit" form="del-img-{{ $img->id }}"
                        class="absolute -top-2 -right-2 bg-red-600 text-white rounded-full w-6 h-6 text-xs">×</button>
                @if ($img->is_cover)
                    <span class="absolute bottom-1 left-1 rounded bg-indigo-600 px-1.5 py-0.5 text-[10px] text-white">Cover</span>
                @else
                    <button type="submit" form="cover-img-{{ $img->id }}" class="mt-1 text-xs text-indigo-600">Set as cover</button>
                @endif
            </div>
        @endforeach
    </div>
@endif
