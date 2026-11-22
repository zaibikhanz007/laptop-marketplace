<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $period = $filters['period'] ?? 'monthly';

        $query = Order::with('laptop')->where('status', 'completed');
        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }

        $orders = $query->get();
        $summary = [
            'orders_count' => $orders->count(),
            'total_sales' => (int) $orders->sum(fn ($order) => $order->laptop->price ?? 0),
        ];

        $grouped = $orders->groupBy(function (Order $order) use ($period) {
            if ($period === 'daily') {
                return $order->created_at->format('Y-m-d');
            }
            if ($period === 'weekly') {
                return $order->created_at->format('Y') . '-W' . $order->created_at->format('W');
            }
            return $order->created_at->format('Y-m');
        });

        $rows = $grouped->map(function ($group, $key) {
            return [
                'period' => $key,
                'orders_count' => $group->count(),
                'total_sales' => (int) $group->sum(fn ($order) => $order->laptop->price ?? 0),
            ];
        })->sortKeys()->values();

        return view('admin.reports.index', compact('period', 'summary', 'rows'));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly'])],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
        $period = $filters['period'] ?? 'monthly';
        $query = Order::with('laptop')->where('status', 'completed');
        if (isset($filters['from'])) {
            $query->whereDate('created_at', '>=', $filters['from']);
        }
        if (isset($filters['to'])) {
            $query->whereDate('created_at', '<=', $filters['to']);
        }
        $orders = $query->get();

        $grouped = $orders->groupBy(function (Order $order) use ($period) {
            if ($period === 'daily') {
                return $order->created_at->format('Y-m-d');
            }
            if ($period === 'weekly') {
                return $order->created_at->format('Y') . '-W' . $order->created_at->format('W');
            }
            return $order->created_at->format('Y-m');
        });

        return response()->streamDownload(function () use ($grouped) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Period', 'Orders', 'Sales']);
            foreach ($grouped->sortKeys() as $period => $group) {
                fputcsv($handle, [
                    $period,
                    $group->count(),
                    $group->sum(fn ($order) => $order->laptop->price ?? 0),
                ]);
            }
            fclose($handle);
        }, 'sales-report.csv', ['Content-Type' => 'text/csv']);
    }
}
