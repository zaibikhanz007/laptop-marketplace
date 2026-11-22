<x-admin-layout>
@php
    $inventoryLabels = $chartData['inventory_labels'] ?: ['No available laptops'];
    $inventoryValues = $chartData['inventory_values'] ?: [0];
@endphp

<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
        <div>
            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-indigo-600">Overview</p>
            <h1 class="text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Admin Dashboard</h1>
            <p class="mt-1 text-sm text-slate-500">Monitor inventory, process orders, and manage marketplace operations.</p>
        </div>
        <div class="flex items-center gap-2.5 self-start sm:self-auto">
            <div class="flex items-center gap-2 rounded-xl border border-slate-200/80 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 shadow-sm">
                <x-admin-icon name="calendar" class="h-3.5 w-3.5 text-indigo-500" />
                <span>{{ now()->format('D, M d, Y') }}</span>
            </div>
            <a href="{{ route('admin.reports.export', ['period' => 'monthly']) }}" class="flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-indigo-700">
                <x-admin-icon name="download" class="h-3.5 w-3.5" />
                <span>Export</span>
            </a>
        </div>
    </div>

    <section aria-label="Primary metrics" class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 sm:gap-5">
        <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Sales</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">Rs. {{ number_format($stats['total_sales']) }}</h2>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-emerald-100 bg-emerald-50 text-emerald-600">
                    <x-admin-icon name="wallet" class="h-5 w-5" />
                </span>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700">Completed orders</span>
                <span class="text-[11px] text-slate-400">PKR revenue</span>
            </div>
        </article>

        <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-blue-600 to-indigo-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Orders</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($stats['total_orders']) }}</h2>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-blue-100 bg-blue-50 text-blue-600">
                    <x-admin-icon name="orders" class="h-5 w-5" />
                </span>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-semibold text-blue-700 hover:bg-blue-100">
                    {{ number_format($stats['pending_orders']) }} awaiting review
                </a>
                <span class="text-[11px] text-slate-400">All time</span>
            </div>
        </article>

        <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-indigo-600 to-violet-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Available Laptops</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($stats['active_laptops']) }}</h2>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-indigo-100 bg-indigo-50 text-indigo-600">
                    <x-admin-icon name="laptop" class="h-5 w-5" />
                </span>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                <a href="{{ route('admin.laptops.index', ['status' => 'available']) }}" class="rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700 hover:bg-indigo-100">Live inventory</a>
                <span class="text-[11px] text-slate-400">{{ number_format($stats['total_laptops']) }} total listings</span>
            </div>
        </article>

        <article class="group relative overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg">
            <div class="absolute left-0 top-0 h-1 w-full bg-gradient-to-r from-cyan-500 to-teal-500"></div>
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Total Users</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl">{{ number_format($stats['total_users']) }}</h2>
                </div>
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-cyan-100 bg-cyan-50 text-cyan-600">
                    <x-admin-icon name="users" class="h-5 w-5" />
                </span>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                <span class="rounded-full bg-cyan-50 px-2 py-0.5 text-[11px] font-semibold text-cyan-700">{{ number_format($stats['verified_users']) }} verified accounts</span>
                <span class="text-[11px] text-slate-400">Buyers & sellers</span>
            </div>
        </article>
    </section>

    <section aria-label="Operational status metrics" class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 sm:gap-4 lg:grid-cols-6">
        <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg border border-amber-100 bg-amber-50 text-amber-600"><x-admin-icon name="users" class="h-4 w-4" /></span>
            <span class="block text-xl font-bold text-slate-900">{{ number_format($stats['pending_users']) }}</span>
            <span class="mt-0.5 block text-xs font-medium text-slate-500">Pending Users</span>
        </a>
        <a href="{{ route('admin.laptops.index', ['status' => 'pending']) }}" class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg border border-amber-100 bg-amber-50 text-amber-600"><x-admin-icon name="laptop" class="h-4 w-4" /></span>
            <span class="block text-xl font-bold text-slate-900">{{ number_format($stats['pending_laptops']) }}</span>
            <span class="mt-0.5 block text-xs font-medium text-slate-500">Pending Laptops</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="rounded-xl border border-amber-300/80 bg-amber-50/40 p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="mb-3 flex items-center justify-between">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg border border-amber-200 bg-amber-100 text-amber-700"><x-admin-icon name="clock" class="h-4 w-4" /></span>
                @if ($stats['pending_orders'] > 0)<span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-bold text-white">Action needed</span>@endif
            </span>
            <span class="block text-xl font-extrabold text-slate-900">{{ number_format($stats['pending_orders']) }}</span>
            <span class="mt-0.5 block text-xs font-semibold text-amber-900">Pending Orders</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'accepted']) }}" class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg border border-sky-100 bg-sky-50 text-sky-600"><x-admin-icon name="orders" class="h-4 w-4" /></span>
            <span class="block text-xl font-bold text-slate-900">{{ number_format($stats['accepted_orders']) }}</span>
            <span class="mt-0.5 block text-xs font-medium text-slate-500">Accepted Orders</span>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'completed']) }}" class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg border border-emerald-100 bg-emerald-50 text-emerald-600"><x-admin-icon name="check" class="h-4 w-4" /></span>
            <span class="block text-xl font-bold text-slate-900">{{ number_format($stats['completed_orders']) }}</span>
            <span class="mt-0.5 block text-xs font-medium text-slate-500">Completed Orders</span>
        </a>
        <a href="{{ route('admin.complaints.index', ['status' => 'open']) }}" class="rounded-xl border border-slate-200/80 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
            <span class="mb-3 flex h-8 w-8 items-center justify-center rounded-lg border border-rose-100 bg-rose-50 text-rose-600"><x-admin-icon name="complaints" class="h-4 w-4" /></span>
            <span class="block text-xl font-bold text-slate-900">{{ number_format($stats['open_complaints']) }}</span>
            <span class="mt-0.5 block text-xs font-medium text-slate-500">Open Complaints</span>
        </a>
    </section>

    <section class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <article class="flex flex-col rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6 lg:col-span-2">
            <div class="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
                <div>
                    <h2 class="font-bold text-slate-900">Inventory & Orders Overview</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Live listing mix and this week's order activity</p>
                </div>
                <div class="inline-flex self-start rounded-xl bg-slate-100 p-1">
                    <button type="button" onclick="switchAdminDashboardChart('inventory', this)" class="admin-chart-tab rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm">Inventory Mix</button>
                    <button type="button" onclick="switchAdminDashboardChart('orders', this)" class="admin-chart-tab rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800">Weekly Orders</button>
                </div>
            </div>
            <div class="relative h-60 w-full">
                <canvas
                    id="admin-store-analytics-chart"
                    data-inventory-labels="{{ json_encode($inventoryLabels) }}"
                    data-inventory-values="{{ json_encode($inventoryValues) }}"
                    data-order-labels="{{ json_encode($chartData['orders_labels']) }}"
                    data-order-values="{{ json_encode($chartData['orders_values']) }}"
                    aria-label="Inventory and orders chart"
                    role="img"
                ></canvas>
            </div>
        </article>

        <article class="flex flex-col justify-between rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm sm:p-6">
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h2 class="font-bold text-slate-900">Quick Actions</h2>
                    <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-[11px] font-semibold text-indigo-600">Admin Tools</span>
                </div>
                <div class="space-y-2.5">
                    <a href="{{ route('admin.laptops.index', ['status' => 'pending']) }}" class="group flex items-center justify-between rounded-xl border border-slate-200/80 p-3 transition hover:border-indigo-300 hover:bg-indigo-50/40">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 transition-transform group-hover:scale-105"><x-admin-icon name="laptop" class="h-4 w-4" /></span>
                            <span><span class="block text-xs font-bold text-slate-800">Review Laptop Listings</span><span class="block text-[11px] text-slate-500">Moderate submitted inventory</span></span>
                        </span>
                        <x-admin-icon name="arrow" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-indigo-600" />
                    </a>
                    <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="group flex items-center justify-between rounded-xl border border-slate-200/80 p-3 transition hover:border-amber-300 hover:bg-amber-50/40">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-600 transition-transform group-hover:scale-105"><x-admin-icon name="orders" class="h-4 w-4" /></span>
                            <span><span class="block text-xs font-bold text-slate-800">Review Pending Orders</span><span class="block text-[11px] text-slate-500">{{ number_format($stats['pending_orders']) }} waiting for action</span></span>
                        </span>
                        <x-admin-icon name="arrow" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-amber-600" />
                    </a>
                    <a href="{{ route('admin.reports.index') }}" class="group flex items-center justify-between rounded-xl border border-slate-200/80 p-3 transition hover:border-emerald-300 hover:bg-emerald-50/40">
                        <span class="flex items-center gap-3">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600 transition-transform group-hover:scale-105"><x-admin-icon name="reports" class="h-4 w-4" /></span>
                            <span><span class="block text-xs font-bold text-slate-800">View Store Reports</span><span class="block text-[11px] text-slate-500">Sales and completed orders</span></span>
                        </span>
                        <x-admin-icon name="arrow" class="h-4 w-4 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-emerald-600" />
                    </a>
                </div>
            </div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3.5 text-xs">
                <span class="text-slate-500">Marketplace status</span>
                <span class="flex items-center gap-1 font-semibold text-emerald-600"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Live</span>
            </div>
        </article>
    </section>

    <section aria-label="Actionable queues" class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-slate-900">Pending Orders</h2>
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-bold text-amber-700">{{ $pendingOrders->count() }}</span>
                </div>
                <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700">View all <x-admin-icon name="arrow" class="h-3.5 w-3.5" /></a>
            </div>
            <div class="space-y-3 p-5">
                @forelse ($pendingOrders as $order)
                    <div x-show="!adminSearch || $el.innerText.toLowerCase().includes(adminSearch.toLowerCase())" class="flex flex-col justify-between gap-4 rounded-xl border border-slate-200/80 bg-slate-50/90 p-4 transition hover:bg-slate-100/70 sm:flex-row sm:items-center">
                        <div class="flex min-w-0 items-start gap-3.5 sm:items-center">
                            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-slate-800 to-slate-900 text-indigo-300 shadow-sm"><x-admin-icon name="laptop" class="h-6 w-6" /></span>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold text-sm text-slate-900">#{{ $order->id }} · {{ $order->laptop->brand }} {{ $order->laptop->model }}</span>
                                    <span class="rounded-full border border-amber-200/70 bg-amber-100 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Pending</span>
                                </div>
                                <p class="mt-1 text-xs text-slate-500">{{ $order->buyer->name }} <span class="px-1 text-slate-400">→</span> {{ $order->seller->name }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $order->created_at->format('d M Y, H:i') }} · Rs. {{ number_format($order->laptop->price) }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 flex-wrap items-center gap-2 self-end sm:self-center">
                            <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50">View</a>
                            <form method="POST" action="{{ route('admin.orders.accept', $order) }}">
                                @csrf @method('PATCH')
                                <button class="flex items-center gap-1 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700"><x-admin-icon name="check" class="h-3.5 w-3.5" /> Accept</button>
                            </form>
                            <form method="POST" action="{{ route('admin.orders.reject', $order) }}" onsubmit="return confirm('Reject this order?')">
                                @csrf @method('PATCH')
                                <button class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 hover:bg-rose-100">Reject</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="flex min-h-36 flex-col items-center justify-center text-center">
                        <span class="mb-2 flex h-11 w-11 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 text-emerald-600"><x-admin-icon name="check" class="h-5 w-5" /></span>
                        <p class="text-sm font-semibold text-slate-700">No pending orders</p>
                        <p class="mt-0.5 text-xs text-slate-400">New purchase requests will appear here.</p>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-slate-900">Pending Users</h2>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $pendingUsers->count() }}</span>
                </div>
                <a href="{{ route('admin.users.index', ['status' => 'pending']) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Verification Queue</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($pendingUsers as $user)
                    <a x-show="!adminSearch || $el.innerText.toLowerCase().includes(adminSearch.toLowerCase())" href="{{ route('admin.users.show', $user) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                        <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-800">{{ $user->name }}</span><span class="block truncate text-xs text-slate-500">{{ $user->email }}</span></span>
                        <span class="shrink-0 rounded-lg bg-slate-100 px-3 py-1.5 text-xs font-semibold text-indigo-700">Review</span>
                    </a>
                @empty
                    <div class="flex min-h-36 flex-col items-center justify-center px-5 text-center">
                        <span class="mb-2 flex h-11 w-11 items-center justify-center rounded-full border border-emerald-100 bg-emerald-50 text-emerald-600"><x-admin-icon name="check" class="h-5 w-5" /></span>
                        <p class="text-sm font-semibold text-slate-700">No pending users</p>
                        <p class="mt-0.5 text-xs text-slate-400">All caught up.</p>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-slate-900">Pending Laptops</h2>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $pendingLaptops->count() }}</span>
                </div>
                <a href="{{ route('admin.laptops.index', ['status' => 'pending']) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Moderation</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($pendingLaptops as $laptop)
                    <a x-show="!adminSearch || $el.innerText.toLowerCase().includes(adminSearch.toLowerCase())" href="{{ route('admin.laptops.show', $laptop) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                        <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-800">{{ $laptop->brand }} {{ $laptop->model }}</span><span class="block truncate text-xs text-slate-500">{{ $laptop->seller->name }} · {{ $laptop->location }}</span></span>
                        <span class="shrink-0 rounded-lg bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700">Review</span>
                    </a>
                @empty
                    <div class="flex min-h-36 flex-col items-center justify-center px-5 text-center">
                        <span class="mb-2 flex h-11 w-11 items-center justify-center rounded-full border border-indigo-100 bg-indigo-50 text-indigo-600"><x-admin-icon name="laptop" class="h-5 w-5" /></span>
                        <p class="text-sm font-semibold text-slate-700">No pending listings</p>
                        <p class="mt-0.5 text-xs text-slate-400">Submitted laptops will appear here.</p>
                    </div>
                @endforelse
            </div>
        </article>

        <article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4">
                <div class="flex items-center gap-2.5">
                    <h2 class="font-bold text-slate-900">Open Complaints</h2>
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">{{ $openComplaints->count() }}</span>
                </div>
                <a href="{{ route('admin.complaints.index', ['status' => 'open']) }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-700">Dispute Center</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($openComplaints as $complaint)
                    <a x-show="!adminSearch || $el.innerText.toLowerCase().includes(adminSearch.toLowerCase())" href="{{ route('admin.complaints.show', $complaint) }}" class="flex items-center justify-between gap-3 px-5 py-3 transition hover:bg-slate-50">
                        <span class="min-w-0"><span class="block truncate text-sm font-semibold text-slate-800">{{ $complaint->subject }}</span><span class="block truncate text-xs text-slate-500">{{ $complaint->complainant->name }} vs {{ $complaint->against->name }}</span></span>
                        <span class="shrink-0 rounded-lg bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700">Review</span>
                    </a>
                @empty
                    <div class="flex min-h-36 flex-col items-center justify-center px-5 text-center">
                        <span class="mb-2 flex h-11 w-11 items-center justify-center rounded-full border border-teal-100 bg-teal-50 text-teal-600"><x-admin-icon name="complaints" class="h-5 w-5" /></span>
                        <p class="text-sm font-semibold text-slate-700">No open complaints</p>
                        <p class="mt-0.5 text-xs text-slate-400">There are no active disputes.</p>
                    </div>
                @endforelse
            </div>
        </article>
    </section>
</div>

<script>
    let adminDashboardChart;
    let adminDashboardChartType = 'inventory';

    function renderAdminDashboardChart() {
        const canvas = document.getElementById('admin-store-analytics-chart');
        if (!canvas || !window.Chart) return;

        const inventoryLabels = JSON.parse(canvas.dataset.inventoryLabels);
        const inventoryValues = JSON.parse(canvas.dataset.inventoryValues);
        const orderLabels = JSON.parse(canvas.dataset.orderLabels);
        const orderValues = JSON.parse(canvas.dataset.orderValues);
        const isInventory = adminDashboardChartType === 'inventory';

        if (adminDashboardChart) adminDashboardChart.destroy();
        adminDashboardChart = new window.Chart(canvas, {
            type: isInventory ? 'bar' : 'line',
            data: {
                labels: isInventory ? inventoryLabels : orderLabels,
                datasets: [{
                    label: isInventory ? 'Available laptops' : 'Orders placed',
                    data: isInventory ? inventoryValues : orderValues,
                    backgroundColor: isInventory
                        ? ['rgba(79, 70, 229, .85)', 'rgba(14, 165, 233, .85)', 'rgba(99, 102, 241, .75)', 'rgba(16, 185, 129, .85)', 'rgba(139, 92, 246, .85)', 'rgba(245, 158, 11, .8)']
                        : 'rgba(79, 70, 229, .12)',
                    borderColor: isInventory ? 'transparent' : '#4f46e5',
                    borderWidth: isInventory ? 0 : 2.5,
                    borderRadius: 8,
                    tension: .4,
                    fill: !isInventory,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: isInventory ? 0 : 4,
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 10 },
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#64748b' } },
                    y: { beginAtZero: true, ticks: { precision: 0, color: '#64748b' }, grid: { color: '#f1f5f9' } },
                },
            },
        });
    }

    function switchAdminDashboardChart(type, button) {
        adminDashboardChartType = type;
        document.querySelectorAll('.admin-chart-tab').forEach(tab => {
            tab.className = 'admin-chart-tab rounded-lg px-3 py-1.5 text-xs font-semibold text-slate-500 transition hover:text-slate-800';
        });
        button.className = 'admin-chart-tab rounded-lg bg-white px-3 py-1.5 text-xs font-semibold text-slate-800 shadow-sm';
        renderAdminDashboardChart();
    }

    document.addEventListener('DOMContentLoaded', renderAdminDashboardChart);
</script>
</x-admin-layout>
