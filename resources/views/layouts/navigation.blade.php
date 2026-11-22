<nav x-data="{ open: false }" class="lapmarket-nav-shell relative z-50">
    <div class="lapmarket-nav max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between min-h-16 px-4 sm:px-5 gap-3">
            <div class="flex items-center gap-5 xl:gap-8 min-w-0">
                <a href="{{ route('home') }}" class="lapmarket-brand shrink-0">
                    <span class="lapmarket-brand-mark" aria-hidden="true">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                    </span>
                    <span class="lapmarket-brand-text">Lap<span>Market</span></span>
                </a>
                <div class="hidden lg:flex items-center gap-1 overflow-x-auto">
                    <a href="{{ route('home') }}" class="lapmarket-nav-link {{ request()->routeIs('home', 'laptops.*') ? 'is-active' : '' }}">Marketplace</a>
                    @auth
                        <a href="{{ route('orders.my') }}" class="lapmarket-nav-link {{ request()->routeIs('orders.my') ? 'is-active' : '' }}">My Orders</a>
                        <a href="{{ route('orders.incoming') }}" class="lapmarket-nav-link {{ request()->routeIs('orders.incoming') ? 'is-active' : '' }}">Incoming Requests @php($pendingIncomingCount = \App\Models\Order::where('seller_id', auth()->id())->where('status', 'pending')->count()) @if ($pendingIncomingCount > 0)<span class="lapmarket-count">{{ $pendingIncomingCount }}</span>@endif</a>
                        <a href="{{ route('complaints.index') }}" class="lapmarket-nav-link {{ request()->routeIs('complaints.*') ? 'is-active' : '' }}">My Complaints</a>
                        <a href="{{ route('wishlist.index') }}" class="lapmarket-nav-link {{ request()->routeIs('wishlist.*') ? 'is-active' : '' }}">Favourites</a>
                        @if (auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="lapmarket-nav-link {{ request()->routeIs('admin.*') ? 'is-active' : '' }}">Admin</a>@endif
                    @endauth
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @auth
                    <a href="{{ route('notifications.index') }}" class="lapmarket-icon-button" aria-label="Notifications"><svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" /></svg></a>
                    <div class="relative hidden sm:block">
                        <button @click="open = ! open" class="lapmarket-profile" type="button"><span class="lapmarket-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span><span class="max-w-24 truncate">{{ auth()->user()->name }}</span><svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7" /></svg></button>
                        <div x-cloak x-show="open" @click.outside="open = false" x-transition class="lapmarket-profile-menu"><div class="px-3.5 py-2 border-b border-white/10"><p class="font-semibold text-white text-xs">Signed in as</p><p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p></div><a href="{{ route('profile.edit') }}">Profile</a><form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Log Out</button></form></div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="lapmarket-nav-link">Log in</a><a href="{{ route('register') }}" class="lapmarket-auth-button">Register</a>
                @endauth
                <button @click="open = ! open" class="lapmarket-menu-button lg:hidden" type="button" aria-label="Toggle menu"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg></button>
            </div>
        </div>
        <div x-cloak x-show="open" class="lg:hidden border-t border-white/10 px-3 py-3 space-y-1">
            <a href="{{ route('home') }}" class="lapmarket-mobile-link">Marketplace</a>
            @auth
                <a href="{{ route('orders.my') }}" class="lapmarket-mobile-link">My Orders</a><a href="{{ route('orders.incoming') }}" class="lapmarket-mobile-link">Incoming Requests</a><a href="{{ route('complaints.index') }}" class="lapmarket-mobile-link">My Complaints</a><a href="{{ route('wishlist.index') }}" class="lapmarket-mobile-link">Favourites</a>
                @if (auth()->user()->isAdmin())<a href="{{ route('admin.dashboard') }}" class="lapmarket-mobile-link">Admin</a>@endif
            @endauth
        </div>
    </div>
</nav>
