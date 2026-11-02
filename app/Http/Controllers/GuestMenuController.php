<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GuestMenuController extends Controller
{
    public function index(Request $request): View
    {
        $selectedTable = null;
        if ($request->filled('meja')) {
            $selectedTable = DiningTable::where('number', strtoupper(trim((string) $request->query('meja'))))->first();
        }

        // Eager Loading: active categories with ordered child menus
        $categories = Category::active()
            ->ordered()
            ->with(['menus' => fn ($q) => $q->orderByDesc('is_available')->orderByDesc('is_featured')->orderBy('name')])
            ->get()
            ->filter(fn (Category $category) => $category->menus->isNotEmpty())
            ->values();

        $featuredMenus = Menu::with('category')
            ->available()
            ->featured()
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->orderBy('category_id')
            ->take(6)
            ->get();

        return view('guest.menu-book', [
            'categories' => $categories,
            'featuredMenus' => $featuredMenus,
            'selectedTable' => $selectedTable,
            'serviceRate' => (float) config('restaurant.service_charge_rate', 0.05),
            'taxRate' => (float) config('restaurant.tax_rate', 0.10),
        ]);
    }
}
