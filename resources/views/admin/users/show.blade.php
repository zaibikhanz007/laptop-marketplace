<x-admin-layout>
    <div class="mb-6 flex items-center justify-between">
        <div><p class="text-sm text-gray-500">User profile</p><h1 class="text-3xl font-bold">{{ $user->name }}</h1></div>
        <a href="{{ route('admin.users.index') }}" class="text-sm text-indigo-600">Back to users</a>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <section class="lg:col-span-1 bg-white border rounded-lg p-5">
            <dl class="space-y-3 text-sm">
                <div><dt class="text-gray-500">Email</dt><dd>{{ $user->email }}</dd></div>
                <div><dt class="text-gray-500">City</dt><dd>{{ $user->city ?: '-' }}</dd></div>
                <div><dt class="text-gray-500">Role</dt><dd>{{ ucfirst($user->role) }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd>{{ ucfirst($user->status) }}</dd></div>
                <div><dt class="text-gray-500">Seller verification</dt><dd>{{ $user->is_verified ? 'Verified' : 'Not verified' }}</dd></div>
                @if($user->blocked_reason)<div><dt class="text-gray-500">Blocked reason</dt><dd>{{ $user->blocked_reason }}</dd></div>@endif
            </dl>
            @if($user->role !== 'admin')
                <div class="mt-5 space-y-3">
                    <form method="POST" action="{{ route('admin.users.verify',$user) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="is_verified" value="{{ $user->is_verified ? 0 : 1 }}">
                        <button class="w-full px-3 py-2 {{ $user->is_verified ? 'bg-gray-700' : 'bg-blue-600' }} text-white rounded-md">
                            {{ $user->is_verified ? 'Remove Verification' : 'Verify Seller' }}
                        </button>
                    </form>
                    @if($user->status === 'pending')
                        <form method="POST" action="{{ route('admin.users.approve',$user) }}">@csrf @method('PATCH')<button class="w-full px-3 py-2 bg-green-600 text-white rounded-md">Approve</button></form>
                        <form method="POST" action="{{ route('admin.users.reject',$user) }}" onsubmit="return confirm('Reject and block this user?')">@csrf @method('PATCH')<button class="w-full px-3 py-2 bg-red-600 text-white rounded-md">Reject / Block</button></form>
                    @elseif($user->status === 'active')
                        <form method="POST" action="{{ route('admin.users.block',$user)}}" class="space-y-2">
                            @csrf @method('PATCH')
                            <textarea name="blocked_reason" required minlength="3" maxlength="1000" class="w-full border-gray-300 rounded-md" placeholder="Blocking reason"></textarea>
                            @error('blocked_reason')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                            <button class="w-full px-3 py-2 bg-red-600 text-white rounded-md">Block User</button>
                        </form>
                    @elseif($user->status === 'blocked')
                        <form method="POST" action="{{ route('admin.users.unblock',$user)}}" onsubmit="return confirm('Unblock this user?')">@csrf @method('PATCH')<button class="w-full px-3 py-2 bg-green-600 text-white rounded-md">Unblock</button></form>
                    @endif
                </div>
            @else
                <p class="mt-5 text-sm text-gray-500">Administrator accounts cannot be moderated.</p>
            @endif
        </section>
        <section class="lg:col-span-2 space-y-6">
            <div class="bg-white border rounded-lg p-5">
                <h2 class="font-semibold mb-3">Laptops</h2>
                @forelse($laptops as $laptop)<div class="py-2 border-t text-sm flex justify-between"><span>{{ $laptop->brand }} {{ $laptop->model }}</span><span>{{ ucfirst($laptop->status) }}</span></div>@empty<p class="text-sm text-gray-500">No laptops.</p>@endforelse
            </div>
            <div class="bg-white border rounded-lg p-5">
                <h2 class="font-semibold mb-3">Recent Orders</h2>
                @forelse($orders as $order)<div class="py-2 border-t text-sm flex justify-between"><span>{{ $order->laptop->brand }} {{ $order->laptop->model }}</span><span>{{ ucfirst($order->status) }}</span></div>@empty<p class="text-sm text-gray-500">No orders.</p>@endforelse
            </div>
            <div class="bg-white border rounded-lg p-5">
                <h2 class="font-semibold mb-3">Reviews Received</h2>
                @forelse($reviews as $review)<div class="py-2 border-t text-sm"><p>{{ str_repeat('★',$review->rating) }}{{ str_repeat('☆',5-$review->rating) }} - {{ $review->comment ?: 'No comment' }}</p></div>@empty<p class="text-sm text-gray-500">No reviews.</p>@endforelse
            </div>
        </section>
    </div>
</x-admin-layout>
