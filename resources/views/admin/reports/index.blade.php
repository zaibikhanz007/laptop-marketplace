<x-admin-layout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div><p class="text-sm text-gray-500">Sales analytics</p><h1 class="text-3xl font-bold">Sales Report</h1></div>
        <a href="{{ route('admin.reports.export', array_filter(request()->only(['period','from','to']))) }}" class="px-4 py-2 bg-gray-900 text-white rounded-md text-sm text-center">Export CSV</a>
    </div>
    <form method="GET" class="mb-6 bg-white border rounded-lg p-4 flex flex-wrap gap-3">
        <select name="period" class="border-gray-300 rounded-md"><option value="daily" @selected($period==='daily')>Daily</option><option value="weekly" @selected($period==='weekly')>Weekly</option><option value="monthly" @selected($period==='monthly')>Monthly</option></select>
        <input type="date" name="from" value="{{ request('from') }}" class="border-gray-300 rounded-md">
        <input type="date" name="to" value="{{ request('to') }}" class="border-gray-300 rounded-md">
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Apply</button>
    </form>
    <div class="grid sm:grid-cols-2 gap-4">
        <div class="bg-white border rounded-lg p-5"><p class="text-sm text-gray-500">Completed orders</p><p class="mt-2 text-3xl font-bold">{{ number_format($summary['orders_count']) }}</p></div>
        <div class="bg-white border rounded-lg p-5"><p class="text-sm text-gray-500">Total sales</p><p class="mt-2 text-3xl font-bold">Rs. {{ number_format($summary['total_sales']) }}</p></div>
    </div>
    <div class="mt-6 grid lg:grid-cols-2 gap-6">
        <section class="bg-white border rounded-lg p-5"><h2 class="font-semibold mb-4">Sales by period</h2><canvas id="sales-chart" height="180"></canvas></section>
        <section class="bg-white border rounded-lg p-5 overflow-x-auto"><table class="min-w-full divide-y"><thead class="bg-gray-50"><tr><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Period</th><th class="px-4 py-3 text-left text-xs uppercase text-gray-500">Orders</th><th class="px-4 py-3 text-right text-xs uppercase text-gray-500">Sales</th></tr></thead><tbody class="divide-y">@forelse($rows as $row)<tr><td class="px-4 py-3">{{ $row['period'] }}</td><td class="px-4 py-3">{{ $row['orders_count'] }}</td><td class="px-4 py-3 text-right">Rs. {{ number_format($row['total_sales']) }}</td></tr>@empty<tr><td colspan="3" class="px-4 py-8 text-center text-gray-500">No completed sales.</td></tr>@endforelse</tbody></table></section>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!window.Chart) return;
            const canvas = document.getElementById('sales-chart');
            if (!canvas) return;
            new window.Chart(canvas, {
                type: 'bar',
                data: {
                    labels: @json($rows->pluck('period')),
                    datasets: [{
                        label: 'Sales (Rs.)',
                        data: @json($rows->pluck('total_sales')),
                        backgroundColor: '#4f46e5',
                        borderRadius: 6
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
            });
        });
    </script>
</x-admin-layout>
