<x-admin-layout>
    <div class="mb-6"><p class="text-sm text-gray-500">Trust & safety</p><h1 class="text-3xl font-bold">Complaints</h1></div>
    <form method="GET" class="mb-6"><select name="status" class="border-gray-300 rounded-md"><option value="">All statuses</option>@foreach(['open','warned','blocked','dismissed'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select><button class="ml-2 px-4 py-2 bg-indigo-600 text-white rounded-md">Filter</button></form>
    <div class="space-y-3">
        @forelse($complaints as $complaint)
            @php $badge=['open'=>'bg-yellow-100 text-yellow-800','warned'=>'bg-orange-100 text-orange-800','blocked'=>'bg-red-100 text-red-800','dismissed'=>'bg-gray-200 text-gray-700'][$complaint->status] ?? 'bg-gray-100'; @endphp
            <a href="{{ route('admin.complaints.show',$complaint) }}" class="block bg-white border rounded-lg p-5 hover:border-indigo-300"><div class="flex justify-between gap-4"><div><p class="font-semibold">{{ $complaint->subject }}</p><p class="text-sm text-gray-500">{{ $complaint->complainant->name }} reported {{ $complaint->against->name }}</p></div><span class="px-2.5 py-1 h-fit rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($complaint->status) }}</span></div></a>
        @empty<p class="bg-white border rounded-lg p-10 text-center text-gray-500">No complaints found.</p>@endforelse
    </div>
    <div class="mt-6">{{ $complaints->links() }}</div>
</x-admin-layout>
