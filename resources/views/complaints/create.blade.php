<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-10">
        <div class="mb-6">
            <p class="text-sm text-gray-500">Trust & safety</p>
            <h1 class="text-3xl font-bold text-gray-900">Report {{ $against->name }}</h1>
        </div>

        @if (session('success'))
            <div class="mb-5 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-green-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('complaints.store', $against) }}" class="bg-white p-6 rounded-lg shadow space-y-5">
            @csrf
            <div>
                <label for="subject" class="block text-sm font-semibold text-gray-800">Subject</label>
                <input id="subject" name="subject" value="{{ old('subject') }}" required maxlength="150" class="mt-2 w-full border-gray-300 rounded-md">
                @error('subject')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-semibold text-gray-800">Description</label>
                <textarea id="description" name="description" rows="6" required maxlength="2000" class="mt-2 w-full border-gray-300 rounded-md" placeholder="Describe what happened">{{ old('description') }}</textarea>
                @error('description')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="flex justify-end gap-3">
                <a href="{{ route('sellers.show', $against) }}" class="px-4 py-2 text-gray-600">Cancel</a>
                <button type="submit" class="px-5 py-2 rounded-md bg-red-600 text-white hover:bg-red-700">Submit Complaint</button>
            </div>
        </form>
    </div>
</x-app-layout>
