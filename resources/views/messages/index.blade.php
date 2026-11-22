<x-app-layout>
    <div class="mx-auto max-w-3xl px-4 py-8">
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <a href="{{ auth()->user()->isAdmin() ? route('admin.orders.index') : (auth()->id() === $order->buyer_id ? route('orders.my') : route('orders.incoming')) }}" class="text-sm text-indigo-600 hover:text-indigo-800">&larr; Back to orders</a>
                <h1 class="mt-2 text-2xl font-bold text-gray-900">Messages</h1>
                <p class="text-sm text-gray-500">{{ $order->laptop->brand }} {{ $order->laptop->model }} · Order #{{ $order->id }}</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-700">{{ ucfirst($order->status) }}</span>
        </div>

        <div class="rounded-lg bg-white p-4 shadow sm:p-6">
            <div class="max-h-[28rem] space-y-4 overflow-y-auto" aria-live="polite">
                @forelse ($order->messages as $message)
                    @php($mine = $message->sender_id === auth()->id())
                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-2xl px-4 py-3 {{ $mine ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                            <p class="mb-1 text-xs font-semibold {{ $mine ? 'text-indigo-100' : 'text-gray-500' }}">{{ $message->sender->name }}</p>
                            <p class="whitespace-pre-wrap break-words text-sm">{{ $message->body }}</p>
                            <p class="mt-2 text-right text-xs {{ $mine ? 'text-indigo-100' : 'text-gray-500' }}">{{ $message->created_at->format('d M Y, H:i') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="py-10 text-center text-sm text-gray-500">No messages yet. Start the conversation.</p>
                @endforelse
            </div>

            @if (!auth()->user()->isAdmin())
                <form method="POST" action="{{ route('messages.store', $order) }}" class="mt-6 border-t border-gray-100 pt-5">
                    @csrf
                    <label for="body" class="sr-only">Message</label>
                    <textarea id="body" name="body" rows="3" maxlength="2000" required placeholder="Write a message..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('body') }}</textarea>
                    <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    <div class="mt-3 flex items-center justify-between gap-3">
                        <p class="text-xs text-gray-500">Maximum 2000 characters.</p>
                        <button type="submit" class="rounded-md bg-indigo-600 px-5 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Send message</button>
                    </div>
                </form>
            @else
                <p class="mt-6 border-t border-gray-100 pt-5 text-sm text-gray-500">Admins can read this conversation but cannot send messages.</p>
            @endif
        </div>
    </div>
</x-app-layout>
