<x-admin-layout>
    <div class="mb-6">
        <p class="text-sm text-gray-500">Order management</p>
        <h1 class="text-3xl font-bold">Orders</h1>
        <p class="mt-1 text-sm text-gray-600">Admins can help process pending orders and close accepted transactions.</p>
    </div>

    <form method="GET" class="mb-6 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
        <select name="status" class="border-gray-300 rounded-md">
            <option value="">All statuses</option>
            @foreach (['pending', 'accepted', 'rejected', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" class="border-gray-300 rounded-md">
        <input type="date" name="to" value="{{ request('to') }}" class="border-gray-300 rounded-md">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Filter</button>
    </form>

    <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Order</th>
                    <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Buyer / Seller</th>
                    <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                    <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Date</th>
                    <th class="px-5 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    @php
                        $badge = [
                            'pending' => 'bg-yellow-100 text-yellow-800',
                            'accepted' => 'bg-blue-100 text-blue-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            'completed' => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-gray-200 text-gray-700',
                        ][$order->status] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <tr>
                        <td class="px-5 py-4 font-medium">
                            #{{ $order->id }} · {{ $order->laptop->brand }} {{ $order->laptop->model }}
                        </td>
                        <td class="px-5 py-4 text-sm">
                            {{ $order->buyer->name }} / {{ $order->seller->name }}
                        </td>
                        <td class="px-5 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($order->status) }}</span>
                        </td>
                        <td class="px-5 py-4 text-sm text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-5 py-4">
                            <div class="flex min-w-48 flex-wrap justify-end gap-2">
                                @if ($order->status === 'pending')
                                    <form method="POST" action="{{ route('admin.orders.accept', $order) }}">
                                        @csrf @method('PATCH')
                                        <button class="rounded-md bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">Accept</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.orders.reject', $order) }}" onsubmit="return confirm('Reject this order?')">
                                        @csrf @method('PATCH')
                                        <button class="rounded-md bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">Reject</button>
                                    </form>
                                @elseif ($order->status === 'accepted')
                                    <form method="POST" action="{{ route('admin.orders.complete', $order) }}" onsubmit="return confirm('Mark this transaction as completed?')">
                                        @csrf @method('PATCH')
                                        <button class="rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-indigo-700">Complete</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this accepted order?')">
                                        @csrf @method('PATCH')
                                        <button class="rounded-md border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">Cancel</button>
                                    </form>
                                @endif
                                <a href="{{ route('admin.orders.show', $order) }}" class="rounded-md border px-3 py-1.5 text-xs font-medium text-indigo-700 hover:bg-indigo-50">View</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-10 text-center text-gray-500">No orders found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $orders->links() }}</div>
</x-admin-layout>
