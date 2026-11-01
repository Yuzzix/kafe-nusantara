<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = $this->filteredOrdersQuery($request);

        $unpaidCount = (clone $query)->where('payment_status', Order::PAYMENT_UNPAID)
            ->where('status', '!=', Order::STATUS_CANCELLED)
            ->count();
        $paidCount = (clone $query)->where('payment_status', Order::PAYMENT_PAID)->count();

        // Active running orders surface first, followed by newest orders
        $orders = $query
            ->orderByRaw("CASE WHEN status IN ('pending', 'preparing', 'served') THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Available dates from existing orders for the date dropdown filter
        $availableDates = Order::selectRaw('DATE(created_at) as order_date')
            ->distinct()
            ->orderByDesc('order_date')
            ->limit(35)
            ->pluck('order_date');

        return view('orders.index', compact(
            'orders',
            'unpaidCount',
            'paidCount',
            'availableDates',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $orders = $this->filteredOrdersQuery($request)
            ->orderByDesc('created_at')
            ->get();

        $filename = 'pesanan-kafe-nusantara-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($orders): void {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Microsoft Excel opens Indonesian characters cleanly
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, [
                'No. Order', 'Waktu', 'Pelanggan', 'Tipe', 'Meja',
                'Status Dapur', 'Status Bayar', 'Kasir/Staf',
                'Subtotal (Rp)', 'Diskon (Rp)', 'Service (Rp)', 'PB1 (Rp)', 'Total (Rp)',
            ]);

            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_number,
                    $order->created_at->format('Y-m-d H:i:s'),
                    $order->customer_name ?: 'Tanpa nama',
                    $order->type_label,
                    $order->diningTable?->number ?: '-',
                    $order->status_label,
                    $order->payment_status_label,
                    $order->user?->name ?: '-',
                    $order->subtotal,
                    $order->discount,
                    $order->service_charge,
                    $order->tax,
                    $order->total,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function create(Request $request): View
    {
        // Only active categories and available menus may be ordered
        $categories = Category::active()
            ->ordered()
            ->with([
                'availableMenus' => fn ($q) => $q->orderByDesc('is_featured')->orderBy('name'),
            ])
            ->get()
            ->filter(fn (Category $category) => $category->availableMenus->isNotEmpty())
            ->values();

        // Only free tables can be picked for a new Dine-In order
        $availableTables = DiningTable::where('status', DiningTable::STATUS_AVAILABLE)
            ->orderBy('id')
            ->get();

        $preselectedTableId = $request->filled('table')
            ? $availableTables->firstWhere('number', (string) $request->query('table'))?->id
            : ($availableTables->first()?->id);

        return view('orders.create', [
            'categories' => $categories,
            'availableTables' => $availableTables,
            'preselectedTableId' => $preselectedTableId,
            'taxRate' => (float) config('restaurant.tax_rate', 0.10),
            'serviceRate' => (float) config('restaurant.service_rate', 0.05),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:100'],
            'order_type' => ['required', Rule::in([Order::TYPE_DINE_IN, Order::TYPE_TAKEAWAY])],
            'dining_table_id' => [
                Rule::requiredIf(fn () => $request->input('order_type') === Order::TYPE_DINE_IN),
                'nullable',
                'integer',
                'exists:dining_tables,id',
            ],
            'discount' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array'],
            'items.*.quantity' => ['nullable', 'integer', 'min:0', 'max:99'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ], [
            'dining_table_id.required' => 'Silakan pilih meja tersedia untuk pesanan Makan di Tempat.',
            'items.required' => 'Pilih minimal satu menu untuk membuat pesanan.',
        ]);

        // Keep only menu items with quantity >= 1
        $requestedItems = collect($validated['items'])
            ->map(fn (array $row, int|string $menuId) => [
                'menu_id' => (int) $menuId,
                'quantity' => (int) ($row['quantity'] ?? 0),
                'notes' => isset($row['notes']) && trim((string) $row['notes']) !== ''
                    ? trim((string) $row['notes'])
                    : null,
            ])
            ->filter(fn (array $row) => $row['quantity'] >= 1)
            ->values();

        if ($requestedItems->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal 1 porsi menu sebelum menyimpan pesanan.',
            ]);
        }

        // Load and verify all requested menus are available & in active categories
        $menus = Menu::with('category')
            ->whereIn('id', $requestedItems->pluck('menu_id'))
            ->get()
            ->keyBy('id');

        foreach ($requestedItems as $row) {
            $menu = $menus->get($row['menu_id']);
            if (! $menu || ! $menu->is_available || ! $menu->category?->is_active) {
                throw ValidationException::withMessages([
                    'items' => 'Salah satu menu yang dipilih sedang habis atau tidak aktif.',
                ]);
            }
        }

        $order = DB::transaction(function () use ($validated, $requestedItems, $menus) {
            $tableId = null;

            if ($validated['order_type'] === Order::TYPE_DINE_IN) {
                $table = DiningTable::lockForUpdate()->findOrFail($validated['dining_table_id']);

                if ($table->status !== DiningTable::STATUS_AVAILABLE) {
                    throw ValidationException::withMessages([
                        'dining_table_id' => "Meja {$table->number} baru saja terisi/dipesan. Silakan pilih meja lain.",
                    ]);
                }

                $tableId = $table->id;
            }

            $itemPayloads = $requestedItems->map(function (array $row) use ($menus) {
                /** @var Menu $menu */
                $menu = $menus->get($row['menu_id']);
                $unitPrice = $menu->price; // Price snapshot at order time!

                return [
                    'menu_id' => $menu->id,
                    'quantity' => $row['quantity'],
                    'unit_price' => $unitPrice,
                    'subtotal' => $row['quantity'] * $unitPrice,
                    'notes' => $row['notes'],
                ];
            });

            $subtotal = (int) $itemPayloads->sum('subtotal');
            $discount = (int) ($validated['discount'] ?? 0);
            $totals = Order::calculateTotals($subtotal, $validated['order_type'], $discount);

            $staff = User::currentShiftStaff();

            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $staff?->id,
                'dining_table_id' => $tableId,
                'customer_name' => filled($validated['customer_name'] ?? null)
                    ? trim((string) $validated['customer_name'])
                    : null,
                'order_type' => $validated['order_type'],
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_UNPAID,
                ...$totals,
                'notes' => $validated['notes'] ?? null,
            ]);

            $now = now();
            OrderItem::insert($itemPayloads->map(fn (array $item) => [
                ...$item,
                'order_id' => $order->id,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all());

            if ($tableId) {
                DiningTable::where('id', $tableId)->update([
                    'status' => DiningTable::STATUS_OCCUPIED,
                ]);
            }

            return $order;
        });

        return redirect()
            ->route('orders.show', $order->order_number)
            ->with('success', "Pesanan {$order->order_number} berhasil dibuat dan dikirim ke dapur!");
    }

    public function show(Order $order): View
    {
        // Eager Loading: items -> menu -> category, diningTable, user, and payments
        $order->load(['items.menu.category', 'diningTable', 'user', 'payments']);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Order::STATUS_PENDING,
                Order::STATUS_PREPARING,
                Order::STATUS_SERVED,
                Order::STATUS_CANCELLED,
            ])],
        ]);

        if ($order->isPaid()) {
            return back()->with('error', 'Pesanan yang sudah lunas tidak dapat diubah lagi status dapurnya.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return back()->with('error', 'Pesanan ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($order, $validated): void {
            $order->update([
                'status' => $validated['status'],
            ]);

            if ($validated['status'] === Order::STATUS_CANCELLED && $order->dining_table_id) {
                $this->syncTableOccupancy($order->dining_table_id);
            }
        });

        return back()->with('success', "Status dapur pesanan {$order->order_number} diperbarui menjadi {$order->fresh()->status_label}.");
    }

    public function pay(Request $request, Order $order): RedirectResponse
    {
        if ($order->isPaid()) {
            return back()->with('error', 'Pesanan ini sudah berstatus Lunas.');
        }

        if ($order->status === Order::STATUS_CANCELLED) {
            return back()->with('error', 'Pesanan yang dibatalkan tidak dapat menerima pembayaran.');
        }

        $validated = $request->validate([
            'method' => ['required', Rule::in(array_keys(Payment::METHODS))],
            'received_amount' => [
                Rule::requiredIf(fn () => $request->input('method') === Payment::METHOD_CASH),
                'nullable',
                'integer',
                'min:0',
            ],
            'reference_number' => ['nullable', 'string', 'max:100'],
        ], [
            'received_amount.required' => 'Masukkan nominal uang tunai yang diterima dari pelanggan.',
        ]);

        if ($validated['method'] === Payment::METHOD_CASH && (int) $validated['received_amount'] < $order->total) {
            throw ValidationException::withMessages([
                'received_amount' => 'Uang diterima kurang dari total tagihan ('.$order->formatted_total.').',
            ]);
        }

        DB::transaction(function () use ($order, $validated): void {
            $isCash = $validated['method'] === Payment::METHOD_CASH;
            $received = $isCash ? (int) $validated['received_amount'] : null;
            $change = $isCash ? ($received - $order->total) : 0;

            $reference = ! $isCash
                ? (filled($validated['reference_number'] ?? null)
                    ? trim((string) $validated['reference_number'])
                    : strtoupper($validated['method']).'-'.now()->format('ymdHis'))
                : null;

            $staff = User::currentShiftStaff();

            Payment::create([
                'order_id' => $order->id,
                'method' => $validated['method'],
                'amount' => $order->total,
                'received_amount' => $received,
                'change_amount' => $change,
                'reference_number' => $reference,
                'paid_at' => now(),
            ]);

            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'status' => Order::STATUS_COMPLETED,
                'completed_at' => now(),
                'user_id' => $order->user_id ?? $staff?->id,
            ]);

            if ($order->dining_table_id) {
                $this->syncTableOccupancy($order->dining_table_id);
            }
        });

        return back()->with('success', "Pembayaran {$order->order_number} berhasil dikonfirmasi! Status pesanan kini Lunas & Selesai.");
    }

    private function filteredOrdersQuery(Request $request): Builder
    {
        $query = Order::with(['diningTable', 'user'])->withCount('items');

        if ($request->filled('date') && $request->query('date') !== 'all') {
            $date = (string) $request->query('date');
            if ($date === 'today') {
                $query->whereDate('created_at', today());
            } else {
                $query->whereDate('created_at', $date);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->query('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', (string) $request->query('payment_status'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->query('search'));
            $query->where(function (Builder $q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhereHas('diningTable', fn (Builder $tq) => $tq->where('number', 'like', "%{$search}%"));
            });
        }

        return $query;
    }

    private function syncTableOccupancy(int $diningTableId): void
    {
        $table = DiningTable::find($diningTableId);
        if (! $table) {
            return;
        }

        $stillOccupied = $table->activeOrders()->exists();
        if (! $stillOccupied && $table->status === DiningTable::STATUS_OCCUPIED) {
            $table->update(['status' => DiningTable::STATUS_AVAILABLE]);
        }
    }
}
