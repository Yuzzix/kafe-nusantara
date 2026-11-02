<?php

namespace Database\Seeders;

use App\Models\DiningTable;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Seeds orders + order_items + payments that are internally CONSISTENT:
 *  - unit_price on each item = the menu price (snapshot)
 *  - order subtotal/service/tax/total are calculated with Order::calculateTotals()
 *  - completed orders are paid and have a payment; cancelled/active ones are unpaid
 *  - every active dine-in order sits at its own table, and that table is "occupied"
 *  - order numbers are sequential per day (ORD-YYYYMMDD-0001, 0002, ...)
 *
 * Result: ~30 days of sales history (nice for dashboards/reports) + a few
 * orders that are still running right now (nice for the live demo).
 */
class OrderSeeder extends Seeder
{
    private const HISTORY_DAYS = 30;

    private const HISTORY_ORDERS = 90;

    private const DRINK_CATEGORIES = ['kopi', 'non-kopi', 'teh-minuman-segar'];

    private const DRINK_NOTES = ['Less sugar', 'Less ice', 'Tanpa gula', 'Extra shot', 'Panas', 'Gula dipisah'];

    private const FOOD_NOTES = ['Tidak pedas', 'Extra pedas', 'Tanpa bawang', 'Sambal dipisah', 'Porsi kecil untuk anak'];

    private const ORDER_NOTES = ['Tolong disajikan bersamaan', 'Ulang tahun, minta lilin ya', 'Alergi kacang', 'Minta sendok tambahan'];

    private Collection $allMenus;

    private Collection $availableMenus;

    private Collection $staffIds;

    private Collection $tables;

    public function run(): void
    {
        // Fixed seed => every teammate gets the same "random" data.
        mt_srand(2026);
        fake()->seed(2026);

        $this->allMenus = Menu::with('category')->whereHas('category', fn ($q) => $q->where('is_active', true))->get();
        $this->availableMenus = $this->allMenus->where('is_available', true)->values();
        $this->staffIds = User::whereIn('role', [User::ROLE_CASHIER, User::ROLE_WAITER])
            ->where('is_active', true)
            ->pluck('id');
        $this->tables = DiningTable::all();

        DB::transaction(function () {
            $plan = $this->buildPlan()->sortBy(fn ($o) => $o['time']->timestamp)->values();

            // Free tables for active (still running) dine-in orders.
            $freeTables = $this->tables->where('status', DiningTable::STATUS_AVAILABLE)->shuffle()->values();

            foreach ($plan as $entry) {
                $isActive = in_array($entry['status'], Order::ACTIVE_STATUSES, true);

                $tableId = null;
                if ($entry['type'] === Order::TYPE_DINE_IN) {
                    $tableId = $isActive
                        ? $freeTables->shift()?->id           // each active order gets its own table
                        : $this->tables->random()->id;        // history: any table
                }

                // Ran out of free tables? Make it a takeaway instead.
                $type = ($entry['type'] === Order::TYPE_DINE_IN && $tableId === null)
                    ? Order::TYPE_TAKEAWAY
                    : $entry['type'];

                $this->createOrder($entry['time'], $entry['status'], $type, $tableId);
            }

            // Mark tables that have a running order as occupied.
            DiningTable::whereHas('activeOrders')->update(['status' => DiningTable::STATUS_OCCUPIED]);
        });
    }

    /**
     * Decide WHEN each order happens and its final status, before creating
     * anything, so order numbers can be generated in chronological order.
     */
    private function buildPlan(): Collection
    {
        $plan = collect();

        // 1) History: previous days, finished orders (mostly completed, a few cancelled).
        for ($i = 0; $i < self::HISTORY_ORDERS; $i++) {
            $plan->push([
                'time' => $this->randomOpeningTime(today()->subDays(mt_rand(1, self::HISTORY_DAYS))),
                'status' => mt_rand(1, 100) <= 92 ? Order::STATUS_COMPLETED : Order::STATUS_CANCELLED,
                'type' => $this->randomType(),
            ]);
        }

        // 2) Today, earlier: already finished.
        for ($i = 0; $i < 8; $i++) {
            $time = now()->subMinutes(mt_rand(100, 600))->second(mt_rand(0, 59));
            if ($time->isToday()) {
                $plan->push(['time' => $time, 'status' => Order::STATUS_COMPLETED, 'type' => $this->randomType()]);
            }
        }

        // 3) Today, right now: still running (for the live demo / kitchen view).
        $active = [
            [Order::STATUS_PENDING, 5],  [Order::STATUS_PENDING, 8],
            [Order::STATUS_PREPARING, 15], [Order::STATUS_PREPARING, 22], [Order::STATUS_PREPARING, 18],
            [Order::STATUS_SERVED, 35],  [Order::STATUS_SERVED, 50],
        ];
        foreach ($active as $i => [$status, $minutesAgo]) {
            $plan->push([
                'time' => now()->subMinutes($minutesAgo)->second(mt_rand(0, 59)),
                'status' => $status,
                // Last one is a takeaway, the rest dine-in.
                'type' => $i === count($active) - 1 ? Order::TYPE_TAKEAWAY : Order::TYPE_DINE_IN,
            ]);
        }

        return $plan;
    }

    private function createOrder(Carbon $time, string $status, string $type, ?int $tableId): void
    {
        $isToday = $time->isToday();
        $menuPool = $isToday ? $this->availableMenus : $this->allMenus;

        // ----- Build order lines -----
        $items = $menuPool->random(mt_rand(1, min(5, $menuPool->count())))->map(function (Menu $menu) {
            $qty = $this->weighted([1 => 65, 2 => 25, 3 => 10]);

            return [
                'menu_id' => $menu->id,
                'quantity' => $qty,
                'unit_price' => $menu->price,          // snapshot
                'subtotal' => $qty * $menu->price,
                'notes' => mt_rand(1, 100) <= 20 ? $this->noteFor($menu) : null,
            ];
        });

        $subtotal = (int) $items->sum('subtotal');

        // ~10% of orders get a 10% member discount (rounded to Rp 1.000).
        $discount = mt_rand(1, 100) <= 10 ? (int) (round($subtotal * 0.10 / 1000) * 1000) : 0;

        $totals = Order::calculateTotals($subtotal, $type, $discount);

        $completedAt = $status === Order::STATUS_COMPLETED ? $time->copy()->addMinutes(mt_rand(20, 75)) : null;
        if ($completedAt && $completedAt->isFuture()) {
            $completedAt = now();
        }

        $notes = $status === Order::STATUS_CANCELLED
            ? fake()->randomElement(['Dibatalkan: pelanggan berubah pikiran', 'Dibatalkan: menu habis', 'Dibatalkan: pesanan dobel'])
            : (mt_rand(1, 100) <= 10 ? fake()->randomElement(self::ORDER_NOTES) : null);

        // ----- Order -----
        // forceFill so we can set created_at/updated_at to the past.
        $order = new Order;
        $order->forceFill([
            'order_number' => Order::generateOrderNumber($time),
            'user_id' => $this->staffIds->random(),
            'dining_table_id' => $tableId,
            'customer_name' => ($type === Order::TYPE_TAKEAWAY || mt_rand(1, 100) <= 70) ? fake()->firstName() : null,
            'order_type' => $type,
            'status' => $status,
            'payment_status' => $status === Order::STATUS_COMPLETED ? Order::PAYMENT_PAID : Order::PAYMENT_UNPAID,
            ...$totals,
            'notes' => $notes,
            'completed_at' => $completedAt,
            'created_at' => $time,
            'updated_at' => $completedAt ?? $time,
        ])->save();

        // ----- Order items (bulk insert) -----
        OrderItem::insert($items->map(fn ($item) => [
            ...$item,
            'order_id' => $order->id,
            'created_at' => $time,
            'updated_at' => $time,
        ])->all());

        // ----- Payment (only for completed orders) -----
        if ($status === Order::STATUS_COMPLETED) {
            $this->createPayment($order, $completedAt);
        }
    }

    private function createPayment(Order $order, Carbon $paidAt): void
    {
        $method = $this->weighted([
            Payment::METHOD_CASH => 35,
            Payment::METHOD_QRIS => 35,
            Payment::METHOD_EWALLET => 15,
            Payment::METHOD_DEBIT => 10,
            Payment::METHOD_CREDIT => 5,
        ]);

        $received = null;
        $change = 0;
        $reference = null;

        if ($method === Payment::METHOD_CASH) {
            // Customer pays with a "round" amount: next Rp 5.000 / 10.000 / 50.000 / 100.000.
            $options = collect([5000, 10000, 50000, 100000])
                ->map(fn ($step) => (int) (ceil($order->total / $step) * $step))
                ->unique()
                ->values();
            $received = $options->random();
            $change = $received - $order->total;
        } else {
            $prefix = match ($method) {
                Payment::METHOD_QRIS => 'QRIS',
                Payment::METHOD_EWALLET => fake()->randomElement(['GOPAY', 'OVO', 'DANA', 'SPAY']),
                default => 'EDC',
            };
            $reference = $prefix.'-'.$paidAt->format('ymd').'-'.fake()->numerify('######');
        }

        $payment = new Payment;
        $payment->forceFill([
            'order_id' => $order->id,
            'method' => $method,
            'amount' => $order->total,
            'received_amount' => $received,
            'change_amount' => $change,
            'reference_number' => $reference,
            'paid_at' => $paidAt,
            'created_at' => $paidAt,
            'updated_at' => $paidAt,
        ])->save();
    }

    // ------------------------------------------------------------------
    // Small helpers
    // ------------------------------------------------------------------

    /** Random time during opening hours (10:00 - 21:59), busier at lunch & dinner. */
    private function randomOpeningTime(Carbon $day): Carbon
    {
        $hour = $this->weighted([10 => 4, 11 => 10, 12 => 16, 13 => 12, 14 => 6, 15 => 6, 16 => 7, 17 => 9, 18 => 14, 19 => 16, 20 => 10, 21 => 5]);

        return $day->copy()->setTime($hour, mt_rand(0, 59), mt_rand(0, 59));
    }

    private function randomType(): string
    {
        return mt_rand(1, 100) <= 70 ? Order::TYPE_DINE_IN : Order::TYPE_TAKEAWAY;
    }

    private function noteFor(Menu $menu): string
    {
        $isDrink = in_array($menu->category?->slug, self::DRINK_CATEGORIES, true);

        return fake()->randomElement($isDrink ? self::DRINK_NOTES : self::FOOD_NOTES);
    }

    /**
     * Pick a key from [value => weight].
     *
     * @param  array<int|string, int>  $weights
     */
    private function weighted(array $weights): int|string
    {
        $roll = mt_rand(1, array_sum($weights));

        foreach ($weights as $value => $weight) {
            if (($roll -= $weight) <= 0) {
                return $value;
            }
        }

        return array_key_first($weights);
    }
}
