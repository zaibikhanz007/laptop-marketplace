<?php

namespace App\Http\Controllers;

use App\Models\Laptop;
use App\Models\LaptopImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class LaptopController extends Controller
{
    private function rules(): array
    {
        return [
            'brand'       => 'required|string|max:100',
            'model'       => 'required|string|max:150',
            'processor'   => 'nullable|string|max:150',
            'ram'         => 'nullable|string|max:50',
            'storage'     => 'nullable|string|max:50',
            'condition'   => 'required|in:like_new,good,fair,needs_repair',
            'price'       => 'required|integer|min:1|max:2000000000',
            'location'    => 'required|string|max:150',
            'description' => 'nullable|string|max:5000',
            'payment_account_title'  => 'nullable|string|max:100',
            'payment_account_number' => 'nullable|string|max:50',
            'payment_bank_name'      => 'nullable|string|max:100',
            'images'      => 'nullable|array|max:6',
            'images.*'    => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'brand' => ['nullable', 'string', 'max:100'],
            'condition' => ['nullable', Rule::in(['like_new', 'good', 'fair', 'needs_repair'])],
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
        $wishlistedIds = auth()->check()
            ? auth()->user()->wishlists()->pluck('laptop_id')->all()
            : [];
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

        $existingOrder = null;
        if (auth()->check()) {
            $existingOrder = $laptop->orders()
                ->where('buyer_id', auth()->id())
                ->latest()
                ->first();
        }
        $isWishlisted = auth()->check()
            && auth()->user()->wishlists()->where('laptop_id', $laptop->id)->exists();
        $compareIds = session('compare_laptops', []);

        return view('laptops.show', compact('laptop', 'existingOrder', 'isWishlisted', 'compareIds'));
    }

    public function mine()
    {
        $laptops = auth()->user()->laptops()->with('images')->latest()->paginate(10);
        return view('laptops.mine', compact('laptops'));
    }

    public function create()
    {
        return view('laptops.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $laptop = $request->user()->laptops()->make(collect($data)->except('images')->all());
        $laptop->status = 'pending';
        $laptop->approval_status = 'pending';
        $laptop->save();

        foreach ($request->file('images', []) as $file) {
            $this->storeImage($laptop, $file);
        }

        return redirect()->route('laptops.mine')->with('success', 'Laptop submitted. Admin approval ke baad live hoga.');
    }

    public function edit(Laptop $laptop)
    {
        abort_unless($laptop->user_id === auth()->id(), 403);
        return view('laptops.edit', compact('laptop'));
    }

    public function update(Request $request, Laptop $laptop)
    {
        abort_unless($laptop->user_id === auth()->id(), 403);

        $data = $request->validate($this->rules());
        $updateData = collect($data)->except('images')->all();

        if (in_array($laptop->status, ['available', 'sold'], true)) {
            $statusData = $request->validate([
                'status' => ['sometimes', 'required', Rule::in(['available', 'sold'])],
            ]);

            if (array_key_exists('status', $statusData)) {
                $updateData['status'] = $statusData['status'];
            }
        }

        $laptop->fill($updateData);
        if (array_key_exists('status', $statusData ?? [])) {
            $laptop->status = $statusData['status'];
        }
        $laptop->save();

        foreach ($request->file('images', []) as $file) {
            $this->storeImage($laptop, $file);
        }

        return redirect()->route('laptops.mine')->with('success', 'Laptop updated.');
    }

    public function destroy(Laptop $laptop)
    {
        abort_unless($laptop->user_id === auth()->id() || auth()->user()->isAdmin(), 403);

        foreach ($laptop->images as $img) {
            Storage::disk('public')->delete($img->path);
        }
        $laptop->delete();

        return back()->with('success', 'Laptop removed.');
    }

    public function destroyImage(LaptopImage $image)
    {
        abort_unless($image->laptop->user_id === auth()->id(), 403);
        $laptop = $image->laptop;
        $wasCover = $image->is_cover;
        Storage::disk('public')->delete($image->path);
        $image->delete();
        if ($wasCover) {
            $laptop->images()->oldest()->first()?->update(['is_cover' => true]);
        }

        return back()->with('success', 'Image removed.');
    }

    public function setCover(Laptop $laptop, LaptopImage $image)
    {
        abort_unless($laptop->user_id === auth()->id(), 403);
        abort_unless($image->laptop_id === $laptop->id, 404);

        $laptop->images()->update(['is_cover' => false]);
        $image->update(['is_cover' => true]);

        return back()->with('success', 'Cover image updated.');
    }

    private function storeImage(Laptop $laptop, \Illuminate\Http\UploadedFile $file): void
    {
        $image = $laptop->images()->make(['path' => $file->store('laptops', 'public')]);
        $image->is_cover = ! $laptop->images()->where('is_cover', true)->exists();
        $image->save();
    }
}
