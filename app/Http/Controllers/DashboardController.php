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
        // Today's paid revenue metrics (counts both Pay-Upfront and Pay-After-Eating!)
        $todayPaidQuery = Order::where('payment_status', Order::PAYMENT_PAID)
            ->whereDate('created_at', today());
        $todayRevenue = (int) (clone $todayPaidQuery)->sum('total');
        $todayCompletedCount = (clone $todayPaidQuery)->count();

        // 30-day paid revenue summary
        $monthRevenue = (int) Order::where('payment_status', Order::PAYMENT_PAID)
            ->where('created_at', '>=', today()->subDays(29))
            ->sum('total');
        $monthOrdersCount = Order::where('payment_status', Order::PAYMENT_PAID)
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
        $recentCompleted = Order::where('payment_status', Order::PAYMENT_PAID)
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

    /** Global workspace search ("Cari di ruang kerja...") filtered by active shift role. */
    public function search(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));
        $staff = User::currentShiftStaff();

        $categories = collect();
        $menus = collect();
        $orders = collect();

        if ($q !== '') {
            if (! $staff || $staff->canManageCatalog()) {
                $categories = Category::withCount('menus')
                    ->where(function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    })
                    ->orderBy('sort_order')
                    ->limit(6)
                    ->get();
            }

            if (! $staff || $staff->canViewMenuCatalog() || $staff->canAccessOrders()) {
                $menus = Menu::with('category')
                    ->where(function ($query) use ($q) {
                        $query->where('name', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    })
                    ->orderByDesc('is_featured')
                    ->limit(10)
                    ->get();
            }

            if (! $staff || $staff->canAccessOrders()) {
                $orders = Order::with(['diningTable', 'user'])
                    ->where(function ($query) use ($q) {
                        $query->where('order_number', 'like', "%{$q}%")
                            ->orWhere('customer_name', 'like', "%{$q}%");
                    })
                    ->latest()
                    ->limit(10)
                    ->get();
            }
        }

        return view('dashboard.search', compact('q', 'categories', 'menus', 'orders'));
    }

    /** Switch active staff member on shift from the sidebar footer and adapt workspace route. */
    public function switchStaff(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $staff = User::where('is_active', true)->findOrFail($validated['staff_id']);
        session(['active_staff_id' => $staff->id]);

        $prevPath = trim((string) parse_url(url()->previous(), PHP_URL_PATH), '/');
        $canStayOnCurrentPage = match (true) {
            $prevPath === '' => $staff->canViewDashboard(),
            $prevPath === 'dapur' => $staff->canViewKitchen(),
            str_starts_with($prevPath, 'categories') => $staff->canManageCatalog(),
            str_starts_with($prevPath, 'menus/') => $staff->canManageCatalog(),
            $prevPath === 'menus' => $staff->canViewMenuCatalog(),
            $prevPath === 'orders/export' => $staff->canProcessPayment(),
            str_starts_with($prevPath, 'orders') => $staff->canAccessOrders(),
            str_starts_with($prevPath, 'tables') => $staff->canManageTables(),
            default => true,
        };

        $message = "Shift aktif dialihkan ke {$staff->name} ({$staff->role_label}).";

        if (! $canStayOnCurrentPage) {
            return redirect()->route($staff->defaultRouteName())->with('success', $message);
        }

        return back()->with('success', $message);
    }
}
