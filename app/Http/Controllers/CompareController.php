<?php

namespace App\Http\Controllers;

use App\Models\Laptop;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompareController extends Controller
{
    private function ids(Request $request): array
    {
        $ids = array_values(array_unique(array_filter(
            $request->session()->get('compare_laptops', []),
            fn ($id) => is_numeric($id)
        )));

        return Laptop::whereIn('id', $ids)
            ->where('status', 'available')
            ->where('approval_status', 'approved')
            ->pluck('id')
            ->all();
    }

    public function add(Request $request, Laptop $laptop): RedirectResponse
    {
        abort_unless($laptop->status === 'available' && $laptop->approval_status === 'approved', 422, 'Only available laptops can be compared.');

        $ids = $this->ids($request);
        if (! in_array($laptop->id, $ids)) {
            abort_if(count($ids) >= 3, 422, 'You can compare up to three laptops.');
            $ids[] = $laptop->id;
        }
        $request->session()->put('compare_laptops', $ids);

        return back()->with('success', 'Laptop added to comparison.');
    }

    public function remove(Request $request, Laptop $laptop): RedirectResponse
    {
        $request->session()->put('compare_laptops', array_values(array_diff($this->ids($request), [$laptop->id])));
        return back()->with('success', 'Laptop removed from comparison.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget('compare_laptops');
        return back()->with('success', 'Comparison cleared.');
    }

    public function index(Request $request): View
    {
        $laptops = Laptop::with(['images', 'seller' => fn ($query) => $query->withCount('reviewsReceived')->withAvg('reviewsReceived', 'rating')])
            ->whereIn('id', $this->ids($request))
            ->where('status', 'available')
            ->where('approval_status', 'approved')
            ->get();

        return view('compare.index', compact('laptops'));
    }
}
