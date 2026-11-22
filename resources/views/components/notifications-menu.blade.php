@auth
    @php
        $navNotifications = auth()->user()->notifications()->latest()->limit(5)->get();
        $navUnreadCount = auth()->user()->unreadNotifications()->count();
    @endphp
    <div x-data="{ notificationsOpen: false }" class="relative">
        <button type="button" @click="notificationsOpen = !notificationsOpen" class="relative inline-flex p-2 text-gray-600 hover:bg-gray-100 rounded-full" aria-label="Notifications">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 0 0-5-5.9V4a1 1 0 0 0-2 0v1.1A6 6 0 0 0 6 11v3.2a2 2 0 0 1-.6 1.4L4 17h5m6 0a3 3 0 0 1-6 0m6 0H9"/>
            </svg>
            @if ($navUnreadCount)
                <span class="absolute top-0 right-0 min-w-4 h-4 px-1 rounded-full bg-red-600 text-white text-[10px] leading-4 text-center">{{ $navUnreadCount }}</span>
            @endif
        </button>
        <div x-cloak x-show="notificationsOpen" @click.outside="notificationsOpen=false" class="absolute right-0 mt-2 w-80 max-w-[90vw] bg-white rounded-lg shadow-xl border z-50">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <p class="font-semibold text-sm">Notifications</p>
                @if ($navUnreadCount)
                    <form method="POST" action="{{ route('notifications.read-all') }}">@csrf<button class="text-xs text-indigo-600">Mark all read</button></form>
                @endif
            </div>
            <div class="max-h-80 overflow-y-auto divide-y">
                @forelse ($navNotifications as $notification)
                    <a href="{{ route('notifications.read', $notification->id) }}" class="block px-4 py-3 text-sm hover:bg-gray-50 {{ $notification->read_at ? 'text-gray-500' : 'text-gray-900 font-medium' }}">
                        {{ $notification->data['message'] ?? 'You have a new notification.' }}
                        <span class="block mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="px-4 py-6 text-sm text-gray-500 text-center">No notifications yet.</p>
                @endforelse
            </div>
            <a href="{{ route('notifications.index') }}" class="block px-4 py-3 border-t text-sm text-center text-indigo-600">View all notifications</a>
        </div>
    </div>
@endauth
