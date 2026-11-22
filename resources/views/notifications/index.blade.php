<x-app-layout>
    <div class="max-w-4xl mx-auto px-4 py-8 pb-28">
        <div class="flex items-center justify-between mb-6">
            <div><p class="text-sm text-gray-500">Updates</p><h1 class="text-3xl font-bold">Notifications</h1></div>
            <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="text-sm text-indigo-600">Mark all as read</button></form>
        </div>
        @if (session('success'))<div class="mb-5 rounded-lg bg-green-50 p-3 text-green-800">{{ session('success') }}</div>@endif
        <div class="bg-white rounded-lg border shadow-sm divide-y">
            @forelse ($notifications as $notification)
                <a href="{{ route('notifications.read', $notification->id) }}" class="block p-5 hover:bg-gray-50 {{ $notification->read_at ? '' : 'bg-indigo-50/50' }}">
                    <p class="text-sm {{ $notification->read_at ? 'text-gray-600' : 'font-semibold text-gray-900' }}">{{ $notification->data['message'] ?? 'You have a new notification.' }}</p>
                    <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->format('d M Y, H:i') }}</p>
                </a>
            @empty<p class="p-8 text-center text-gray-500">No notifications yet.</p>@endforelse
        </div>
        <div class="mt-6">{{ $notifications->links() }}</div>
    </div>
</x-app-layout>
