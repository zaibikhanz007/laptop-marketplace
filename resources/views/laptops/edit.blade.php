<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Edit Laptop</h1>
        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">{{ session('success') }}</div>
        @endif

        {{-- image delete forms, main form ke bahar (nested form allowed nahi) --}}
        @foreach ($laptop->images as $img)
            <form id="del-img-{{ $img->id }}" method="POST" action="{{ route('laptops.images.destroy', $img) }}">
                @csrf @method('DELETE')
            </form>
            <form id="cover-img-{{ $img->id }}" method="POST" action="{{ route('laptops.images.cover', [$laptop, $img]) }}">
                @csrf @method('PATCH')
            </form>
        @endforeach

        <form method="POST" action="{{ route('laptops.update', $laptop) }}" enctype="multipart/form-data"
              class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf @method('PUT')
            @include('laptops._form')
            <x-primary-button>Update Laptop</x-primary-button>
        </form>
    </div>
</x-app-layout>
