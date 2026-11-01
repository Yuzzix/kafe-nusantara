<?php

namespace App\Http\Controllers;

use App\Models\DiningTable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DiningTableController extends Controller
{
    public function index(): View
    {
        // Eager load activeOrders so occupied tables can link directly to their running order
        $tables = DiningTable::with([
            'activeOrders' => fn ($q) => $q->with('user')->latest(),
        ])->orderBy('id')->get();

        $counts = [
            'available' => $tables->where('status', DiningTable::STATUS_AVAILABLE)->count(),
            'occupied' => $tables->where('status', DiningTable::STATUS_OCCUPIED)->count(),
            'reserved' => $tables->where('status', DiningTable::STATUS_RESERVED)->count(),
        ];

        $areas = [
            'Indoor' => [
                'title' => 'Dalam ruangan',
                'subtitle' => '6 meja · T01–T06',
                'tables' => $tables->where('area', 'Indoor')->values(),
            ],
            'Outdoor' => [
                'title' => 'Luar ruangan',
                'subtitle' => '4 meja · T07–T10',
                'tables' => $tables->where('area', 'Outdoor')->values(),
            ],
            'VIP' => [
                'title' => 'VIP',
                'subtitle' => '2 ruang privat',
                'tables' => $tables->where('area', 'VIP')->values(),
            ],
        ];

        return view('tables.index', compact('tables', 'counts', 'areas'));
    }

    public function updateStatus(Request $request, DiningTable $diningTable): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                DiningTable::STATUS_AVAILABLE,
                DiningTable::STATUS_RESERVED,
            ])],
        ]);

        // Data integrity guard: cannot manually free a table that still has a running order!
        if ($diningTable->activeOrders()->exists()) {
            return back()->with(
                'error',
                "Meja {$diningTable->number} sedang melayani pesanan aktif. Selesaikan atau batalkan pesanannya terlebih dahulu."
            );
        }

        $diningTable->update([
            'status' => $validated['status'],
        ]);

        $label = DiningTable::STATUSES[$validated['status']] ?? $validated['status'];

        return back()->with('success', "Status meja {$diningTable->number} diperbarui menjadi {$label}.");
    }
}
