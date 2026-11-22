<?php

namespace App\Http\Controllers;

use App\Models\Laptop;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LaptopController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'condition' => ['nullable', Rule::in(['new', 'like_new', 'used', 'refurbished', 'good', 'fair', 'needs_repair'])],
            'city' => ['nullable', 'string', 'max:150'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:2000000000'],
            'sort' => ['nullable', Rule::in(['newest', 'price_low', 'price_high'])],
        ]);

        $laptops = Laptop::with(['images'])
            ->with(['seller' => fn ($query) => $query->withCount('reviewsReceived')->withAvg('reviewsReceived', 'rating')])
            ->available()
            ->filter($filters)
            ->sortBy($filters['sort'] ?? null)
            ->paginate(12)
            ->withQueryString();

        $brands = Laptop::available()->distinct()->orderBy('brand')->pluck('brand');
        $cities = Laptop::available()->distinct()->orderBy('location')->pluck('location');
        $featuredLaptops = Laptop::featured()->available()
            ->with(['images', 'seller' => fn ($query) => $query->withCount('reviewsReceived')->withAvg('reviewsReceived', 'rating')])
            ->latest()
            ->limit(4)
            ->get();
        $wishlistedIds = auth()->check() ? auth()->user()->wishlists()->pluck('laptop_id')->all() : [];
        $compareIds = session('compare_laptops', []);

        return view('laptops.index', compact('laptops', 'brands', 'cities', 'featuredLaptops', 'wishlistedIds', 'compareIds'));
    }

    public function show(Laptop $laptop)
    {
        abort_if(
            in_array($laptop->status, ['pending', 'rejected', 'blocked'], true)
            || $laptop->approval_status !== 'approved',
            404
        );

        $laptop->load(['images']);
        $laptop->load(['seller' => fn ($query) => $query->withCount('reviewsReceived')->withAvg('reviewsReceived', 'rating')]);

        $existingOrder = auth()->check()
            ? $laptop->orders()->where('buyer_id', auth()->id())->latest()->first()
            : null;
        $isWishlisted = auth()->check()
            && auth()->user()->wishlists()->where('laptop_id', $laptop->id)->exists();
        $compareIds = session('compare_laptops', []);

        return view('laptops.show', compact('laptop', 'existingOrder', 'isWishlisted', 'compareIds'));
    }
}
