<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\Laptop;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'laptops' => Laptop::count(),
            'pending_laptops' => Laptop::where('approval_status', 'pending')->count(),
            'orders' => Order::count(),
            'open_complaints' => Complaint::where('status', 'open')->count(),
        ];
        $pendingUsers = User::where('status', 'pending')->latest()->limit(5)->get();
        $pendingListings = Laptop::with('seller')->where('approval_status', 'pending')->latest()->limit(5)->get();

        return view('admin.dashboard', compact('stats', 'pendingUsers', 'pendingListings'));
    }

    public function users(Request $request)
    {
        $users = User::query()
            ->when($request->q, fn ($q, $v) => $q->where(fn ($w) => $w->where('name', 'like', "%{$v}%")->orWhere('email', 'like', "%{$v}%")))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function updateUser(Request $request, User $user)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,active,blocked'],
            'role' => ['required', 'in:user,admin'],
        ]);
        $user->update($data);

        return back()->with('success', 'User account updated.');
    }

    public function laptops(Request $request)
    {
        $laptops = Laptop::with('seller')
            ->when($request->approval_status, fn ($q, $v) => $q->where('approval_status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.laptops', compact('laptops'));
    }

    public function updateLaptop(Request $request, Laptop $laptop)
    {
        $data = $request->validate([
            'approval_status' => ['required', 'in:pending,approved,rejected'],
            'status' => ['nullable', 'in:pending,available,sold,rejected,blocked'],
        ]);
        $laptop->update($data);

        return back()->with('success', 'Listing moderation updated.');
    }

    public function complaints(Request $request)
    {
        $complaints = Complaint::with(['complainant', 'against'])
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.complaints', compact('complaints'));
    }

    public function updateComplaint(Request $request, Complaint $complaint)
    {
        $data = $request->validate(['status' => ['required', 'in:open,warned,blocked,dismissed']]);
        $complaint->update($data);

        return back()->with('success', 'Complaint status updated.');
    }

    public function reports()
    {
        $metrics = [
            'users_by_status' => User::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'orders_by_status' => Order::selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'top_sellers' => User::withCount(['sellerOrders as completed_orders' => fn ($q) => $q->where('status', 'completed')])
                ->orderByDesc('completed_orders')
                ->limit(10)
                ->get(),
            'reviews' => Review::count(),
        ];

        return view('admin.reports', compact('metrics'));
    }
}
