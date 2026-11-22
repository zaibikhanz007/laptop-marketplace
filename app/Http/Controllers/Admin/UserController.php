<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Notifications\AccountStatusChanged;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'active', 'blocked'])],
        ]);
        $users = User::query()
            ->when($filters['q'] ?? null, fn ($q, $value) => $q->where(function ($query) use ($value) {
                $query->where('name', 'like', "%{$value}%")
                    ->orWhere('email', 'like', "%{$value}%");
            }))
            ->when($filters['status'] ?? null, fn ($q, $value) => $q->where('status', $value))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user): View
    {
        $user->loadCount(['laptops', 'buyerOrders', 'sellerOrders', 'reviewsReceived', 'complaintsFiled', 'complaintsReceived']);
        $laptops = $user->laptops()->with('images')->latest()->limit(10)->get();
        $orders = $user->buyerOrders()->with(['laptop', 'seller'])->latest()->limit(10)->get();
        $reviews = $user->reviewsReceived()->with(['reviewer', 'order'])->latest()->limit(10)->get();

        return view('admin.users.show', compact('user', 'laptops', 'orders', 'reviews'));
    }

    public function approve(User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 403, 'Admin accounts cannot be moderated this way.');
        $user->status = 'active';
        $user->blocked_reason = null;
        $user->save();
        $user->notify(new AccountStatusChanged('active'));

        return back()->with('success', 'User account approved.');
    }

    public function reject(User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 403, 'Admin accounts cannot be rejected.');

        DB::transaction(function () use ($user) {
            $user->status = 'blocked';
            $user->blocked_reason = 'Account rejected by administrator.';
            $user->save();
            $user->laptops()->where('status', 'available')->update(['status' => 'blocked']);
        });
        $user->notify(new AccountStatusChanged('blocked'));

        return back()->with('success', 'User account rejected and blocked.');
    }

    public function block(BlockUserRequest $request, User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 403, 'Another administrator or your own account cannot be blocked.');

        DB::transaction(function () use ($request, $user) {
            $user->status = 'blocked';
            $user->blocked_reason = $request->validated('blocked_reason');
            $user->save();
            $user->laptops()->where('status', 'available')->update(['status' => 'blocked']);
        });
        $user->notify(new AccountStatusChanged('blocked'));

        return back()->with('success', 'User blocked and available listings blocked.');
    }

    public function unblock(User $user): RedirectResponse
    {
        abort_if($user->role === 'admin', 403, 'Admin accounts cannot be unblocked this way.');
        $user->status = 'active';
        $user->blocked_reason = null;
        $user->save();
        $user->notify(new AccountStatusChanged('active'));

        return back()->with('success', 'User unblocked.');
    }

    public function verify(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate(['is_verified' => ['required', 'boolean']]);
        abort_if($user->role === 'admin', 422, 'Admin accounts cannot be seller-verified.');
        $user->is_verified = $data['is_verified'];
        $user->save();

        return back()->with('success', $data['is_verified'] ? 'Seller verified.' : 'Seller verification removed.');
    }
}
