<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-8 pb-28">
        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif
        <div class="flex items-center justify-between mb-6">
            <div><p class="text-sm text-gray-500">Side-by-side specs</p><h1 class="text-3xl font-bold">Compare Laptops</h1></div>
            @if ($laptops->isNotEmpty())
                <form method="POST" action="{{ route('compare.clear') }}">@csrf @method('DELETE')<button class="text-sm text-red-600">Clear comparison</button></form>
            @endif
        </div>
        @if ($laptops->isEmpty())
            <div class="bg-white rounded-lg p-10 text-center text-gray-500 shadow-sm">Select available laptops to compare them here.</div>
        @else
            @php $lowestPrice = $laptops->min('price'); @endphp
            <div class="overflow-x-auto bg-white rounded-lg shadow border">
                <table class="min-w-[720px] w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="w-40 p-4 bg-gray-50 text-left text-sm text-gray-500">Specification</th>
                            @foreach ($laptops as $laptop)
                                <th class="p-4 text-left align-top border-l">
                                    <a href="{{ route('laptops.show', $laptop) }}" class="font-semibold text-indigo-600">{{ $laptop->brand }} {{ $laptop->model }}</a>
                                    <div class="mt-1 flex items-center gap-2"><x-verified-badge :verified="$laptop->seller->is_verified" /><x-star-rating :rating="$laptop->seller->reviews_received_avg_rating ?? 0" :count="$laptop->seller->reviews_received_count" /></div>
                                    <form method="POST" action="{{ route('compare.remove', $laptop) }}" class="mt-2">@csrf @method('DELETE')<button class="text-xs text-red-600">Remove</button></form>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ([
                            'Brand' => fn ($l) => $l->brand,
                            'Model' => fn ($l) => $l->model,
                            'Processor' => fn ($l) => $l->processor ?: '—',
                            'RAM' => fn ($l) => $l->ram ?: '—',
                            'Storage' => fn ($l) => $l->storage ?: '—',
                            'Condition' => fn ($l) => ucfirst(str_replace('_', ' ', $l->condition)),
                            'Price' => fn ($l) => 'Rs. '.number_format($l->price),
                            'Location' => fn ($l) => $l->location,
                            'Seller rating' => fn ($l) => number_format((float) ($l->seller->reviews_received_avg_rating ?? 0), 1).' / 5',
                        ] as $label => $value)
                            <tr>
                                <th class="p-4 bg-gray-50 text-left text-sm font-medium text-gray-600">{{ $label }}</th>
                                @foreach ($laptops as $laptop)
                                    <td class="p-4 border-l text-sm {{ $label === 'Price' && $laptop->price === $lowestPrice ? 'bg-green-50 font-bold text-green-800' : 'text-gray-700' }}">
                                        {{ $value($laptop) }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
