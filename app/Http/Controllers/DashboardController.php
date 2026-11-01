<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Today's metrics
        $todayCompletedQuery = Order::where('status', Order::STATUS_COMPLETED)
            ->whereDate('created_at', today());
        $todayRevenue = (int) (clone $todayCompletedQuery)->sum('total');
        $todayCompletedCount = (clone $todayCompletedQuery)->count();

        // 30-day revenue summary
        $monthRevenue = (int) Order::where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', today()->subDays(29))
            ->sum('total');
        $monthOrdersCount = Order::where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', today()->subDays(29))
            ->count();

        // Active running orders right now (Eager Loading: diningTable + user + items count)
        $activeOrders = Order::active()
            ->with(['diningTable', 'user'])
            ->withCount('items')
            ->orderBy('created_at')
            ->get();

        // Table occupancy summary
        $tableCounts = [
            'available' => DiningTable::where('status', DiningTable::STATUS_AVAILABLE)->count(),
            'occupied' => DiningTable::where('status', DiningTable::STATUS_OCCUPIED)->count(),
            'reserved' => DiningTable::where('status', DiningTable::STATUS_RESERVED)->count(),
            'total' => DiningTable::count(),
        ];

        // 7-day daily revenue trend (works cleanly on both MySQL & SQLite)
        $recentCompleted = Order::where('status', Order::STATUS_COMPLETED)
            ->where('created_at', '>=', today()->subDays(6)->startOfDay())
            ->get(['total', 'created_at'])
            ->groupBy(fn (Order $order) => $order->created_at->format('Y-m-d'));

        $dailyTrend = collect(range(6, 0))->map(function (int $daysAgo) use ($recentCompleted) {
            $day = today()->subDays($daysAgo);
            $key = $day->format('Y-m-d');
            $group = $recentCompleted->get($key, collect());

            return [
                'date' => $day,
                'label' => $day->translatedFormat('d M'),
                'day_short' => $day->translatedFormat('D'),
                'orders_count' => $group->count(),
                'revenue' => (int) $group->sum('total'),
            ];
        });

        $maxDailyRevenue = max(1, (int) $dailyTrend->max('revenue'));

        // Top 5 Best-Selling Menus (Eager Loading category + aggregate sum on order_items)
        $topMenus = Menu::with('category')
            ->withSum([
                'orderItems as sold_qty' => fn ($q) => $q->whereHas(
                    'order',
                    fn ($oq) => $oq->where('status', '!=', Order::STATUS_CANCELLED)
                ),
            ], 'quantity')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();

        // Catalog summary
        $catalogStats = [
            'categories' => Category::active()->count(),
            'menus' => Menu::count(),
            'unavailable_menus' => Menu::where('is_available', false)->count(),
        ];

        return view('dashboard.index', compact(
            'todayRevenue',
            'todayCompletedCount',
            'monthRevenue',
            'monthOrdersCount',
            'activeOrders',
            'tableCounts',
            'dailyTrend',
            'maxDailyRevenue',
            'topMenus',
            'catalogStats',
        ));
    }

    /** Global workspace search ("Cari di ruang kerja...") */
    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $categories = collect();
        $menus = collect();
        $orders = collect();

        if ($q !== '') {
            $categories = Category::withCount('menus')
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orderBy('sort_order')
                ->limit(6)
                ->get();

            $menus = Menu::with('category')
                ->where('name', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%")
                ->orderByDesc('is_featured')
                ->limit(10)
                ->get();

            $orders = Order::with(['diningTable', 'user'])
                ->where('order_number', 'like', "%{$q}%")
                ->orWhere('customer_name', 'like', "%{$q}%")
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('dashboard.search', compact('q', 'categories', 'menus', 'orders'));
    }

    /** Switch active staff member on shift from the sidebar footer. */
    public function switchStaff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $staff = User::where('is_active', true)->findOrFail($validated['staff_id']);
        session(['active_staff_id' => $staff->id]);

        return back()->with('success', "Shift aktif dialihkan ke {$staff->name} ({$staff->role_label}).");
    }
}
