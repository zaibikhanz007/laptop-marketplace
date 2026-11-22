<x-admin-layout>
    <div class="mb-6 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div><p class="text-sm text-gray-500">Moderation</p><h1 class="text-3xl font-bold">Users</h1></div>
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Search name or email" class="border-gray-300 rounded-md">
            <select name="status" class="border-gray-300 rounded-md">
                <option value="">All statuses</option>
                @foreach(['pending','active','blocked'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach
            </select>
            <button class="px-4 py-2 bg-indigo-600 text-white rounded-md">Filter</button>
        </form>
    </div>
    <div class="bg-white rounded-lg border overflow-x-auto">
        <table class="min-w-full divide-y">
            <thead class="bg-gray-50"><tr>
                <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">User</th>
                <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Role</th>
                <th class="px-5 py-3 text-left text-xs uppercase text-gray-500">Status</th>
                <th class="px-5 py-3 text-right text-xs uppercase text-gray-500">Actions</th>
            </tr></thead>
            <tbody class="divide-y">
            @forelse($users as $user)
                <tr>
                    <td class="px-5 py-4"><p class="font-medium">{{ $user->name }}</p><p class="text-sm text-gray-500">{{ $user->email }}</p></td>
                    <td class="px-5 py-4 text-sm">{{ ucfirst($user->role) }}</td>
                    <td class="px-5 py-4">
                        @php $badge=['pending'=>'bg-yellow-100 text-yellow-800','active'=>'bg-green-100 text-green-800','blocked'=>'bg-red-100 text-red-800'][$user->status] ?? 'bg-gray-100'; @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ ucfirst($user->status) }}</span>
                    </td>
                    <td class="px-5 py-4 text-right"><a href="{{ route('admin.users.show',$user) }}" class="text-indigo-600 text-sm font-medium">View</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-gray-500">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</x-admin-layout>
