<x-admin-layout>
    <div class="mb-6 flex justify-between"><div><p class="text-sm text-gray-500">Complaint</p><h1 class="text-3xl font-bold">{{ $complaint->subject }}</h1></div><a href="{{ route('admin.complaints.index') }}" class="text-indigo-600 text-sm">Back</a></div>
    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-2 bg-white border rounded-lg p-5"><dl class="grid sm:grid-cols-2 gap-4 text-sm"><div><dt class="text-gray-500">Complainant</dt><dd>{{ $complaint->complainant->name }}</dd></div><div><dt class="text-gray-500">Reported user</dt><dd>{{ $complaint->against->name }}</dd></div></dl><div class="mt-5 border-t pt-4"><p class="text-gray-500 text-sm">Description</p><p class="mt-1">{{ $complaint->description }}</p></div>@if($complaint->admin_note)<div class="mt-5 border-t pt-4"><p class="text-gray-500 text-sm">Admin note</p><p class="mt-1">{{ $complaint->admin_note }}</p></div>@endif</section>
        <section class="bg-white border rounded-lg p-5">
            @if($complaint->status === 'open')
                <form method="POST" action="{{ route('admin.complaints.warn',$complaint) }}" class="space-y-3 mb-3">@csrf @method('PATCH')<textarea name="admin_note" maxlength="1000" class="w-full border-gray-300 rounded-md" placeholder="Optional warning note"></textarea><button class="w-full px-3 py-2 bg-yellow-600 text-white rounded-md">Warn</button></form>
                <form method="POST" action="{{ route('admin.complaints.block',$complaint) }}" class="space-y-3 mb-3">@csrf @method('PATCH')<textarea name="blocked_reason" minlength="3" maxlength="1000" class="w-full border-gray-300 rounded-md" placeholder="Blocking reason (optional)"></textarea><textarea name="admin_note" maxlength="1000" class="w-full border-gray-300 rounded-md" placeholder="Optional admin note"></textarea><button class="w-full px-3 py-2 bg-red-600 text-white rounded-md">Block Reported User</button></form>
                <form method="POST" action="{{ route('admin.complaints.dismiss',$complaint) }}" class="space-y-3">@csrf @method('PATCH')<textarea name="admin_note" maxlength="1000" class="w-full border-gray-300 rounded-md" placeholder="Optional dismissal note"></textarea><button class="w-full px-3 py-2 bg-gray-700 text-white rounded-md">Dismiss</button></form>
            @else
                <p class="text-sm text-gray-500">This complaint has already been actioned.</p>
            @endif
        </section>
    </div>
</x-admin-layout>
