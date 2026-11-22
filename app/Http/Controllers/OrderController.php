<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderStoreRequest;
use App\Http\Requests\OrderStatusRequest;
use App\Models\Laptop;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Notifications\NewPurchaseRequest;
use App\Notifications\OrderStatusChanged;

class OrderController extends Controller
{
    public function store(OrderStoreRequest $request, Laptop $laptop): RedirectResponse
    {
        $order = DB::transaction(function () use ($request, $laptop) {
            $lockedLaptop = Laptop::query()->lockForUpdate()->findOrFail($laptop->id);
            Gate::authorize('create', $lockedLaptop);

            $order = new Order([
                'phone' => $request->validated('phone'),
                'address' => $request->validated('address'),
                'payment_method' => $request->validated('payment_method'),
            ]);
            $order->laptop()->associate($lockedLaptop);
            $order->buyer()->associate($request->user());
            $order->seller()->associate($lockedLaptop->seller);
            $order->status = 'pending';
            $order->save();

            return $order->load(['seller', 'laptop']);
        });
        $order->seller->notify(new NewPurchaseRequest($order));

        return redirect()->route('orders.my')->with('success', 'Purchase request sent to the seller.');
    }

    public function myOrders(): View
    {
        $user = request()->user();
        $orders = $user->buyerOrders()
            ->with(['laptop.images', 'seller', 'review'])
            ->withCount(['messages as unread_messages_count' => fn ($query) => $query
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')])
            ->latest()
            ->paginate(10);

        return view('orders.my', compact('orders'));
    }

    public function incoming(): View
    {
        $user = request()->user();
        $orders = $user->sellerOrders()
            ->with(['laptop.images', 'buyer'])
            ->withCount(['messages as unread_messages_count' => fn ($query) => $query
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')])
            ->latest()
            ->paginate(10);

        return view('orders.incoming', compact('orders'));
    }

    public function accept(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order = $this->transition($order, 'accept', 'accepted');
        $order->buyer->notify(new OrderStatusChanged($order));
        $this->notifySellerForAdminAction($request, $order);

        return back()->with('success', 'Purchase request accepted. The buyer can now coordinate with you.');
    }

    public function reject(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order = $this->transition($order, 'reject', 'rejected');
        $order->buyer->notify(new OrderStatusChanged($order));
        $this->notifySellerForAdminAction($request, $order);

        return back()->with('success', 'Purchase request rejected.');
    }

    public function complete(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        [$completedOrder, $cancelledOrders] = DB::transaction(function () use ($order) {
            $lockedOrder = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            Gate::authorize('complete', $lockedOrder);
            $lockedLaptop = Laptop::query()->lockForUpdate()->findOrFail($lockedOrder->laptop_id);
            abort_unless(
                $lockedLaptop->status === 'available' && $lockedLaptop->approval_status === 'approved',
                422,
                'This laptop is no longer available to complete this order.'
            );

            $lockedOrder->status = 'completed';
            $lockedOrder->save();
            $lockedLaptop->status = 'sold';
            $lockedLaptop->save();

            $otherOrders = Order::where('laptop_id', $lockedOrder->laptop_id)
                ->where('id', '!=', $lockedOrder->id)
                ->whereIn('status', ['pending', 'accepted'])
                ->with(['buyer', 'seller'])
                ->get();
            Order::whereKey($otherOrders->modelKeys())->update(['status' => 'cancelled']);

            $lockedOrder->load(['buyer', 'seller']);
            return [$lockedOrder, $otherOrders];
        });
        $order = $completedOrder;
        $order->buyer->notify(new OrderStatusChanged($order));
        $this->notifySellerForAdminAction($request, $order);
        foreach ($cancelledOrders as $cancelledOrder) {
            $cancelledOrder->status = 'cancelled';
            $cancelledOrder->buyer->notify(new OrderStatusChanged($cancelledOrder));
            $this->notifySellerForAdminAction($request, $cancelledOrder);
        }

        return back()->with('success', 'Transaction completed. The buyer can now leave a review.');
    }

    public function cancel(OrderStatusRequest $request, Order $order): RedirectResponse
    {
        $order = $this->transition($order, 'cancel', 'cancelled');
        $order->buyer->notify(new OrderStatusChanged($order));
        $order->seller->notify(new OrderStatusChanged($order));

        return back()->with('success', 'Order cancelled.');
    }

    private function transition(Order $order, string $ability, string $status): Order
    {
        return DB::transaction(function () use ($order, $ability, $status) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            Gate::authorize($ability, $lockedOrder);
            if ($ability === 'accept') {
                $lockedLaptop = Laptop::query()->lockForUpdate()->findOrFail($lockedOrder->laptop_id);
                abort_unless(
                    $lockedLaptop->status === 'available' && $lockedLaptop->approval_status === 'approved',
                    422,
                    'This laptop is no longer available to accept this order.'
                );
            }
            $lockedOrder->status = $status;
            $lockedOrder->save();

            return $lockedOrder->load(['buyer', 'seller']);
        });
    }

    private function notifySellerForAdminAction(OrderStatusRequest $request, Order $order): void
    {
        if ($request->user()->isAdmin()) {
            $order->seller->notify(new OrderStatusChanged($order));
        }
    }
}
