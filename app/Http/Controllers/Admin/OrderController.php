<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'status' => ['nullable', Rule::in(['pending', 'accepted', 'rejected', 'completed', 'cancelled'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $orders = Order::with(['laptop', 'buyer', 'seller'])
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->when($filters['from'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($filters['to'] ?? null, fn ($q, $value) => $q->whereDate('created_at', '<=', $value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['laptop.images', 'buyer', 'seller', 'review']);
        return view('admin.orders.show', compact('order'));
    }
}
