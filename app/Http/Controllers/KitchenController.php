<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\View\View;

class KitchenController extends Controller
{
    public function index(): View
    {
        // Eager Loading: loads order items, menus, table, and cashier in a single batch
        $activeOrders = Order::with(['items.menu', 'diningTable', 'user'])
            ->whereIn('status', Order::ACTIVE_STATUSES)
            ->orderBy('created_at')
            ->get();

        return view('kitchen.index', [
            'pendingOrders' => $activeOrders->where('status', Order::STATUS_PENDING)->values(),
            'preparingOrders' => $activeOrders->where('status', Order::STATUS_PREPARING)->values(),
            'servedOrders' => $activeOrders->where('status', Order::STATUS_SERVED)->values(),
            'totalActiveCount' => $activeOrders->count(),
        ]);
    }
}
