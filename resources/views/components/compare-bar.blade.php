@php
    $compareBarIds = session('compare_laptops', []);
    $compareBarLaptops = \App\Models\Laptop::whereIn('id', $compareBarIds)
        ->where('status', 'available')
        ->where('approval_status', 'approved')
        ->where('approval_status', 'approved')
        ->get();
@endphp

@if ($compareBarLaptops->isNotEmpty())
    <aside class="fixed bottom-0 inset-x-0 z-30 border-t border-indigo-200 bg-white/95 shadow-lg backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 py-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-gray-800">Compare ({{ $compareBarLaptops->count() }}/3):</span>
                @foreach ($compareBarLaptops as $compareLaptop)
                    <form method="POST" action="{{ route('compare.remove', $compareLaptop) }}" class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-3 py-1 text-sm">
                        @csrf @method('DELETE')
                        <span>{{ $compareLaptop->brand }} {{ $compareLaptop->model }}</span>
                        <button aria-label="Remove {{ $compareLaptop->brand }} {{ $compareLaptop->model }}" class="text-gray-500 hover:text-red-600">×</button>
                    </form>
                @endforeach
            </div>
            <div class="flex items-center gap-3">
                <form method="POST" action="{{ route('compare.clear') }}">@csrf @method('DELETE')<button class="text-sm text-gray-500 hover:text-red-600">Clear</button></form>
                <a href="{{ route('compare.index') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Compare now</a>
            </div>
        </div>
    </aside>
@endif
