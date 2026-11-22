<x-app-layout>
    <div class="max-w-6xl mx-auto px-4 py-8">
        <div class="mb-6">
            <p class="text-sm text-gray-500">Seller dashboard</p>
            <h1 class="text-3xl font-bold text-gray-900">Incoming Requests</h1>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="space-y-4">
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
                <article class="bg-white rounded-lg shadow p-5">
                    <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-5">
                        <div class="min-w-0">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('laptops.show', $order->laptop) }}" class="font-semibold text-lg text-indigo-600">
                                    {{ $order->laptop->brand }} {{ $order->laptop->model }}
                                </a>
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            @if ($order->status === 'pending')
                                <p class="mt-2 text-sm text-gray-500">Review the buyer details, then accept or reject this request. The admin can also help process it.</p>
                            @elseif ($order->status === 'accepted')
                                <p class="mt-2 text-sm text-gray-500">After delivery and payment are complete, mark the transaction as completed.</p>
                            @endif
                            <dl class="mt-4 grid sm:grid-cols-2 gap-x-8 gap-y-2 text-sm">
                                <div>
                                    <dt class="text-gray-500">Buyer</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->buyer->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Phone</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->phone }}</dd>
                                </div>
                                <div class="sm:col-span-2">
                                    <dt class="text-gray-500">Address</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->address }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Payment method</dt>
                                    <dd class="font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $order->payment_method)) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-gray-500">Requested</dt>
                                    <dd class="font-medium text-gray-900">{{ $order->created_at->format('d M Y, H:i') }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @if (in_array($order->status, ['pending', 'accepted'], true))
                                <a href="{{ route('messages.index', $order) }}" class="inline-flex items-center px-4 py-2 rounded-md border border-indigo-200 text-indigo-700 hover:bg-indigo-50">
                                    Messages
                                    @if ($order->unread_messages_count > 0)
                                        <span class="ml-1 inline-flex min-w-5 justify-center rounded-full bg-indigo-100 px-1.5 py-0.5 text-xs text-indigo-700">{{ $order->unread_messages_count }}</span>
                                    @endif
                                </a>
                            @endif
                            @if ($order->status === 'pending')
                                <form method="POST" action="{{ route('orders.accept', $order) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-4 py-2 rounded-md bg-green-600 text-white hover:bg-green-700">Accept</button>
                                </form>
                                <form method="POST" action="{{ route('orders.reject', $order) }}">
                                    @csrf @method('PATCH')
                                    <button class="px-4 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Reject</button>
                                </form>
                            @elseif ($order->status === 'accepted')
                                <form method="POST" action="{{ route('orders.complete', $order) }}" onsubmit="return confirm('Mark this transaction complete?')">
                                    @csrf @method('PATCH')
                                    <button class="px-4 py-2 rounded-md bg-indigo-600 text-white hover:bg-indigo-700">Complete</button>
                                </form>
                            @else
                                <span class="text-sm text-gray-400 self-center">No actions available</span>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="bg-white rounded-lg shadow px-6 py-10 text-center text-gray-500">
                    No incoming purchase requests.
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
