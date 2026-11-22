<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectLaptopRequest;
use App\Models\Laptop;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;
use App\Notifications\ListingReviewed;

class LaptopController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'available', 'sold', 'rejected', 'blocked'])],
        ]);
        $laptops = Laptop::with('seller')
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where(function ($query) use ($value) {
                $query->where('brand', 'like', "%{$value}%")
                    ->orWhere('model', 'like', "%{$value}%");
            }))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.laptops.index', compact('laptops'));
    }

    public function show(Laptop $laptop): View
    {
        $laptop->load(['seller', 'images', 'orders.buyer']);
        return view('admin.laptops.show', compact('laptop'));
    }

    public function approve(Laptop $laptop): RedirectResponse
    {
        abort_unless($laptop->status === 'pending', 422, 'Only pending listings can be approved.');
        $laptop->status = 'available';
        $laptop->approval_status = 'approved';
        $laptop->rejection_reason = null;
        $laptop->save();
        $laptop->seller->notify(new ListingReviewed($laptop));

        return back()->with('success', 'Laptop listing approved.');
    }

    public function reject(RejectLaptopRequest $request, Laptop $laptop): RedirectResponse
    {
        abort_unless($laptop->status === 'pending', 422, 'Only pending listings can be rejected.');
        $laptop->status = 'rejected';
        $laptop->approval_status = 'rejected';
        $laptop->rejection_reason = $request->validated('rejection_reason');
        $laptop->save();
        $laptop->seller->notify(new ListingReviewed($laptop));

        return back()->with('success', 'Laptop listing rejected.');
    }

    public function block(Laptop $laptop): RedirectResponse
    {
        abort_unless($laptop->status === 'available', 422, 'Only available listings can be blocked.');
        $laptop->status = 'blocked';
        $laptop->save();

        return back()->with('success', 'Laptop listing blocked.');
    }

    public function unblock(Laptop $laptop): RedirectResponse
    {
        abort_unless($laptop->status === 'blocked', 422, 'Only blocked listings can be unblocked.');
        $laptop->status = 'available';
        $laptop->approval_status = 'approved';
        $laptop->save();

        return back()->with('success', 'Laptop listing unblocked.');
    }

    public function feature(Request $request, Laptop $laptop): RedirectResponse
    {
        $data = $request->validate([
            'is_featured' => ['required', 'boolean'],
            'featured_until' => ['exclude_if:is_featured,0', 'nullable', 'date', 'after_or_equal:today'],
        ]);
        $laptop->is_featured = $data['is_featured'];
        $laptop->featured_until = $data['is_featured'] ? ($data['featured_until'] ?? null) : null;
        $laptop->save();

        return back()->with('success', $data['is_featured'] ? 'Laptop featured.' : 'Laptop removed from featured listings.');
    }

    public function destroy(Laptop $laptop): RedirectResponse
    {
        foreach ($laptop->images as $image) {
            Storage::disk('public')->delete($image->path);
        }
        $laptop->delete();

        return redirect()->route('admin.laptops.index')->with('success', 'Laptop listing deleted.');
    }
}
