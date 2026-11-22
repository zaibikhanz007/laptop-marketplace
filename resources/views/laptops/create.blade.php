<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8">
        <h1 class="text-2xl font-bold mb-6">Sell a Laptop</h1>
        <form method="POST" action="{{ route('laptops.store') }}" enctype="multipart/form-data"
              class="bg-white p-6 rounded-lg shadow space-y-4">
            @csrf
            @include('laptops._form')
            <x-primary-button>List Laptop</x-primary-button>
        </form>
    </div>
</x-app-layout>
