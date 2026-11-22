<x-admin-layout>
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-500">Order management</p>
            <h1 class="text-3xl font-bold">Order #{{ $order->id }}</h1>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 text-sm">Back to orders</a>
    </div>

    @php
        $badge = [
            'pending' => 'bg-yellow-100 text-yellow-800',
            'accepted' => 'bg-blue-100 text-blue-800',
            'rejected' => 'bg-red-100 text-red-800',
            'completed' => 'bg-green-100 text-green-800',
            'cancelled' => 'bg-gray-200 text-gray-700',
        ][$order->status] ?? 'bg-gray-100 text-gray-700';
    @endphp

    <div class="grid gap-6 md:grid-cols-2">
        <section class="rounded-lg border bg-white p-5">
            <h2 class="mb-4 font-semibold">Transaction</h2>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <dt class="text-gray-500">Laptop</dt>
                <dd>{{ $order->laptop->brand }} {{ $order->laptop->model }}</dd>
                <dt class="text-gray-500">Buyer</dt>
                <dd>{{ $order->buyer->name }}</dd>
                <dt class="text-gray-500">Seller</dt>
                <dd>{{ $order->seller->name }}</dd>
                <dt class="text-gray-500">Status</dt>
                <dd><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $badge }}">{{ ucfirst($order->status) }}</span></dd>
                <dt class="text-gray-500">Payment</dt>
                <dd>{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</dd>
                <dt class="text-gray-500">Placed</dt>
                <dd>{{ $order->created_at->format('d M Y, H:i') }}</dd>
            </dl>

            <div class="mt-5 border-t pt-4">
                @if ($order->status === 'pending')
                    <p class="mb-3 text-sm text-gray-600">Pending: accept the request or reject it. Accept only when the seller can fulfill the order.</p>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.orders.accept', $order) }}">
                            @csrf @method('PATCH')
                            <button class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700">Accept order</button>
                        </form>
                        <form method="POST" action="{{ route('admin.orders.reject', $order) }}" onsubmit="return confirm('Reject this order?')">
                            @csrf @method('PATCH')
                            <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Reject order</button>
                        </form>
                    </div>
                @elseif ($order->status === 'accepted')
                    <p class="mb-3 text-sm text-gray-600">Accepted: after delivery and payment are confirmed, mark it complete. You may cancel it if the transaction cannot proceed.</p>
                    <div class="flex flex-wrap gap-2">
                        <form method="POST" action="{{ route('admin.orders.complete', $order) }}" onsubmit="return confirm('Confirm delivery/payment and complete this transaction?')">
                            @csrf @method('PATCH')
                            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Mark completed</button>
                        </form>
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this accepted order?')">
                            @csrf @method('PATCH')
                            <button class="rounded-md border border-red-200 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">Cancel order</button>
                        </form>
                    </div>
                @else
                    <p class="text-sm text-gray-500">This order is {{ $order->status }}; no further status changes are available.</p>
                @endif
            </div>
        </section>

        <section class="rounded-lg border bg-white p-5">
            <h2 class="mb-4 font-semibold">Review</h2>
            @if ($order->review)
                <div>
                    <p>{{ str_repeat('★', $order->review->rating) }}{{ str_repeat('☆', 5 - $order->review->rating) }}</p>
                    <p class="mt-2 text-sm text-gray-700">{{ $order->review->comment ?: 'No comment.' }}</p>
                </div>
            @else
                <p class="text-sm text-gray-500">No review submitted.</p>
            @endif
        </section>
    </div>
</x-admin-layout>
