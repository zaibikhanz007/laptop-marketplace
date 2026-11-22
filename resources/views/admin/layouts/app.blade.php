<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laptop Store') }} - Admin</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#f8fafc] font-sans antialiased text-slate-800 selection:bg-indigo-100 selection:text-indigo-700">
    @php
        $pendingUserCount = \App\Models\User::where('status', 'pending')->count();
        $pendingLaptopCount = \App\Models\Laptop::where('status', 'pending')->count();
        $pendingOrderCount = \App\Models\Order::where('status', 'pending')->count();
        $availableLaptopCount = \App\Models\Laptop::available()->count();
        $adminNavItems = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'pattern' => 'admin.dashboard', 'icon' => 'dashboard'],
            ['label' => 'Users', 'route' => 'admin.users.index', 'pattern' => 'admin.users.*', 'icon' => 'users', 'count' => $pendingUserCount],
            ['label' => 'Laptops', 'route' => 'admin.laptops.index', 'pattern' => 'admin.laptops.*', 'icon' => 'laptop', 'count' => $pendingLaptopCount],
            ['label' => 'Orders', 'route' => 'admin.orders.index', 'pattern' => 'admin.orders.*', 'icon' => 'orders', 'count' => $pendingOrderCount],
            ['label' => 'Complaints', 'route' => 'admin.complaints.index', 'pattern' => 'admin.complaints.*', 'icon' => 'complaints'],
            ['label' => 'Reports', 'route' => 'admin.reports.index', 'pattern' => 'admin.reports.*', 'icon' => 'reports'],
        ];
    @endphp

    <div x-data="{ sidebarOpen: false, adminSearch: '' }" @keydown.window.ctrl.k.prevent="$refs.adminSearch?.focus()" class="min-h-screen lg:flex">
        <div
            x-cloak
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 z-30 bg-slate-900/60 backdrop-blur-sm lg:hidden"
        ></div>

        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-40 flex w-72 shrink-0 select-none flex-col justify-between border-r border-slate-800/80 bg-slate-900 text-slate-300 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
        >
            <div class="p-5">
                <div class="mb-8 flex items-center justify-between px-1">
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-950/30 transition-transform group-hover:scale-105">
                            <x-admin-icon name="laptop" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block font-bold tracking-tight text-white">Laptop Store</span>
                            <span class="block text-[11px] font-medium uppercase tracking-wider text-indigo-400">Admin Console</span>
                        </span>
                    </a>
                    <button type="button" @click="sidebarOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-800 hover:text-white lg:hidden" aria-label="Close admin menu">
                        <x-admin-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                <p class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Main Menu</p>
                <nav aria-label="Admin navigation" class="space-y-1.5">
                    @foreach ($adminNavItems as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @click="sidebarOpen = false"
                            @class([
                                'group flex items-center justify-between rounded-xl px-3.5 py-2.5 text-sm font-medium transition-all',
                                'bg-gradient-to-r from-indigo-600 to-indigo-500 text-white shadow-lg shadow-indigo-950/30' => request()->routeIs($item['pattern']),
                                'text-slate-400 hover:bg-slate-800/70 hover:text-white' => ! request()->routeIs($item['pattern']),
                            ])
                        >
                            <span class="flex items-center gap-3">
                                <x-admin-icon :name="$item['icon']" class="h-4 w-4" />
                                <span>{{ $item['label'] }}</span>
                            </span>
                            @if (($item['count'] ?? 0) > 0)
                                <span @class([
                                    'rounded-full border px-2 py-0.5 text-xs font-bold',
                                    'border-amber-500/30 bg-amber-500/20 text-amber-300' => $item['label'] === 'Orders',
                                    'border-slate-700 bg-slate-800 text-slate-400' => $item['label'] !== 'Orders',
                                ])>{{ $item['count'] }}</span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>

            <div class="border-t border-slate-800/80 p-5">
                <div class="mb-3 rounded-xl border border-slate-800 bg-[#152033] p-3.5">
                    <div class="mb-1.5 flex items-center gap-2.5">
                        <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                        <span class="text-xs font-semibold text-slate-200">Storefront Live</span>
                    </div>
                    <p class="text-[11px] leading-relaxed text-slate-400">{{ number_format($availableLaptopCount) }} approved laptops available to buyers.</p>
                </div>
                <a href="{{ route('laptops.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-700/60 bg-slate-800/60 px-3.5 py-2.5 text-sm font-medium text-slate-300 transition-all hover:bg-slate-800 hover:text-white">
                    <span class="flex items-center gap-2.5">
                        <x-admin-icon name="store" class="h-4 w-4 text-indigo-400" />
                        Back to Marketplace
                    </span>
                    <x-admin-icon name="arrow" class="h-4 w-4 text-slate-400 transition-transform group-hover:translate-x-0.5" />
                </a>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <main class="mx-auto w-full max-w-[1440px] flex-1 space-y-6 p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">{{ session('error') }}</div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
