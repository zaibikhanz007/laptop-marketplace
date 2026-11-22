<x-admin-layout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div><p class="text-sm text-gray-500">Moderation</p><h1 class="text-3xl font-bold">Laptops</h1></div>
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search brand or model" class="border-gray-300 rounded-md">
            <select name="status" class="border-gray-300 rounded-md">
                <option value="">All statuses</option>
                @foreach(['pending','available','sold','rejected','blocked'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
            </select>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Filter</button>
        </form>
    </div>
    <div class="bg-white border rounded-lg overflow-x-auto">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50"><tr><th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Listing</th><th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Seller</th><th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Status</th><th class="px-5 py-3 text-right text-xs uppercase text-gray-500"></th></tr></thead>
            <tbody class="divide-y">
            @forelse($laptops as $laptop)
                @php $badge=['pending'=>'bg-yellow-100 text-yellow-800','available'=>'bg-green-100 text-green-800','sold'=>'bg-gray-200 text-gray-700','rejected'=>'bg-red-100 text-red-800','blocked'=>'bg-red-100 text-red-800'][$laptop->status] ?? 'bg-gray-100'; @endphp
                <tr><td class="px-5 py-4"><p class="font-medium">{{ $laptop->brand }} {{ $laptop->model }}</p><p class="text-sm text-gray-500">Rs. {{ number_format($laptop->price) }}</p></td><td class="px-5 py-4 text-sm">{{ $laptop->seller->name }}</td><td class="px-5 py-4"><span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($laptop->status) }}</span></td><td class="px-5 py-4 text-right"><a href="{{ route('admin.laptops.show',$laptop) }}" class="text-indigo-600 text-sm font-medium">Manage</a></td></tr>
            @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">No listings found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $laptops->links() }}</div>
</x-admin-layout>
