<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <p class="text-sm text-gray-500">Buyer dashboard</p>
                <h1 class="text-3xl font-bold text-gray-900">My Orders</h1>
            </div>
            <a href="{{ route('laptops.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Browse laptops
            </a>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Laptop</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Seller</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Payment</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                        @php
                            $badge = match ($order->status) {
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'accepted' => 'bg-blue-100 text-blue-800',
                                'rejected', 'cancelled' => 'bg-red-100 text-red-800',
                                'completed' => 'bg-green-100 text-green-800',
                                default => 'bg-gray-100 text-gray-700',
                            };
                        @endphp
                        <tr>
                            <td class="px-5 py-4">
                                <a href="{{ route('laptops.show', $order->laptop) }}" class="font-semibold text-indigo-600 hover:text-indigo-800">
                                    {{ $order->laptop->brand }} {{ $order->laptop->model }}
                                </a>
                                <p class="text-sm text-gray-500">Rs. {{ number_format($order->laptop->price) }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm">
                                <p class="font-medium text-gray-900">{{ $order->seller->name }}</p>
                                @if (in_array($order->status, ['accepted', 'completed'], true))
                                    <p class="text-gray-600">{{ $order->seller->phone ?: 'No phone provided' }}</p>
                                    <p class="text-gray-500">{{ $order->seller->city ?: 'No city provided' }}</p>
                                @else
                                    <p class="text-gray-400">Contact hidden until accepted</p>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700">
                                {{ ucwords(str_replace('_', ' ', $order->payment_method)) }}
                                @if ($order->payment_method === 'bank_transfer' && $order->status === 'accepted')
                                    @if ($order->laptop->payment_account_title || $order->laptop->payment_account_number || $order->laptop->payment_bank_name)
                                        <div class="mt-3 rounded-md bg-blue-50 p-3 text-sm text-blue-900">
                                            <p class="font-semibold">Seller bank account</p>
                                            @if ($order->laptop->payment_account_title)
                                                <p>Account title: {{ $order->laptop->payment_account_title }}</p>
                                            @endif
                                            @if ($order->laptop->payment_account_number)
                                                <p>Account number / IBAN: {{ $order->laptop->payment_account_number }}</p>
                                            @endif
                                            @if ($order->laptop->payment_bank_name)
                                                <p>Bank: {{ $order->laptop->payment_bank_name }}</p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="mt-2 text-xs text-gray-500">Seller will share account details</p>
                                    @endif
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                                <p class="mt-1 max-w-xs text-xs text-gray-500">
                                    @if ($order->status === 'pending')
                                        Waiting for the seller or admin to review your request.
                                    @elseif ($order->status === 'accepted')
                                        Coordinate delivery and payment with the seller. The order is completed after the transaction.
                                    @elseif ($order->status === 'completed')
                                        Transaction completed. You can leave a review.
                                    @elseif ($order->status === 'rejected')
                                        The seller or admin declined this request.
                                    @else
                                        This request was cancelled.
                                    @endif
                                </p>
                            </td>
                            <td class="px-5 py-4 text-right">
                                @if (in_array($order->status, ['pending', 'accepted'], true))
                                    <a href="{{ route('messages.index', $order) }}" class="mr-3 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                                        Messages
                                        @if ($order->unread_messages_count > 0)
                                            <span class="ml-1 inline-flex min-w-5 justify-center rounded-full bg-indigo-100 px-1.5 py-0.5 text-xs text-indigo-700">{{ $order->unread_messages_count }}</span>
                                        @endif
                                    </a>
                                    <form method="POST" action="{{ route('orders.cancel', $order) }}" onsubmit="return confirm('Cancel this order?')">
                                        @csrf
                                        @method('PATCH')
                                        <button class="text-sm font-medium text-red-600 hover:text-red-800">Cancel</button>
                                    </form>
                                @elseif ($order->status === 'completed' && !$order->review)
                                    <a href="{{ route('reviews.create', $order) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">Write Review</a>
                                @elseif ($order->status === 'completed')
                                    <span class="text-sm font-medium text-green-700">Reviewed ✓</span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-10 text-center text-gray-500">
                                You have not sent any purchase requests.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
