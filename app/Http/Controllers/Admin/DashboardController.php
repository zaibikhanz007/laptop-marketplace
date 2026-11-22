<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Laptop;
use App\Models\Order;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::where('is_verified', true)->count(),
            'pending_users' => User::where('status', 'pending')->count(),
            'total_laptops' => Laptop::count(),
            'active_laptops' => Laptop::available()->count(),
            'pending_laptops' => Laptop::where('status', 'pending')->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'accepted_orders' => Order::where('status', 'accepted')->count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
            'open_complaints' => Complaint::where('status', 'open')->count(),
            'total_sales' => (int) DB::table('orders')
                ->join('laptops', 'laptops.id', '=', 'orders.laptop_id')
                ->where('orders.status', 'completed')
                ->sum('laptops.price'),
        ];

        $pendingUsers = User::where('status', 'pending')->latest()->limit(5)->get();
        $pendingLaptops = Laptop::with('seller')->where('status', 'pending')->latest()->limit(5)->get();
        $pendingOrders = Order::with(['laptop', 'buyer', 'seller'])->where('status', 'pending')->latest()->limit(5)->get();
        $openComplaints = Complaint::with(['complainant', 'against'])->where('status', 'open')->latest()->limit(5)->get();

        $inventoryByBrand = Laptop::available()
            ->select('brand', DB::raw('COUNT(*) as total'))
            ->groupBy('brand')
            ->orderByDesc('total')
            ->limit(6)
            ->get();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $ordersByDay = Order::whereBetween('created_at', [$weekStart, $weekEnd])
            ->get(['created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'));
        $weekDays = collect(range(0, 6))->map(fn (int $offset) => $weekStart->copy()->addDays($offset));
        $chartData = [
            'inventory_labels' => $inventoryByBrand->pluck('brand')->all(),
            'inventory_values' => $inventoryByBrand->pluck('total')->map(fn ($count) => (int) $count)->all(),
            'orders_labels' => $weekDays->map(fn ($day) => $day->format('D'))->all(),
            'orders_values' => $weekDays->map(fn ($day) => $ordersByDay->get($day->format('Y-m-d'), collect())->count())->all(),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'pendingUsers',
            'pendingLaptops',
            'pendingOrders',
            'openComplaints',
            'chartData'
        ));
    }
}
