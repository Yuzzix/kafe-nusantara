<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An order (pesanan) placed at the restaurant.
 *
 * Order 1 --- * OrderItem * --- 1 Menu
 * Order * --- 1 DiningTable  (nullable, only for dine-in)
 * Order * --- 1 User         (the staff member who took the order)
 * Order 1 --- * Payment
 *
 * All money columns are whole Rupiah (integer).
 */
#[Fillable([
    'order_number', 'user_id', 'dining_table_id', 'customer_name', 'order_type',
    'status', 'payment_status', 'subtotal', 'discount', 'service_charge', 'tax',
    'total', 'notes', 'completed_at',
])]
class Order extends Model
{
    // ----- order_type -----
    public const TYPE_DINE_IN = 'dine_in';
    public const TYPE_TAKEAWAY = 'takeaway';

    public const TYPES = [
        self::TYPE_DINE_IN => 'Makan di Tempat',
        self::TYPE_TAKEAWAY => 'Bawa Pulang',
    ];

    // ----- status (kitchen / service flow) -----
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_SERVED = 'served';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING => 'Menunggu',
        self::STATUS_PREPARING => 'Diproses',
        self::STATUS_SERVED => 'Disajikan',
        self::STATUS_COMPLETED => 'Selesai',
        self::STATUS_CANCELLED => 'Dibatalkan',
    ];

    /** Orders that are still "running" (table is occupied, kitchen is busy). */
    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PREPARING,
        self::STATUS_SERVED,
    ];

    // ----- payment_status -----
    public const PAYMENT_UNPAID = 'unpaid';
    public const PAYMENT_PAID = 'paid';

    public const PAYMENT_STATUSES = [
        self::PAYMENT_UNPAID => 'Belum Bayar',
        self::PAYMENT_PAID => 'Lunas',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'service_charge' => 'integer',
            'tax' => 'integer',
            'total' => 'integer',
            'completed_at' => 'datetime',
        ];
    }

    // ------------------------------------------------------------------
    // Relationships
    // ------------------------------------------------------------------

    /** Staff member (cashier / waiter) who took the order. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function diningTable(): BelongsTo
    {
        return $this->belongsTo(DiningTable::class);
    }

    /** Order lines (detail). Use this for the order detail page. */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Many-to-many shortcut: the menus in this order, with pivot data. */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'subtotal', 'notes'])
            ->withTimestamps()
            ->withTrashed();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // ------------------------------------------------------------------
    // Scopes
    // ------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_STATUSES);
    }

    public function scopeStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    // ------------------------------------------------------------------
    // Business logic helpers
    // ------------------------------------------------------------------

    /**
     * Calculate all money fields from a subtotal.
     *
     *   base           = subtotal - discount
     *   service_charge = base * service_rate        (dine-in only)
     *   tax (PB1)      = (base + service) * tax_rate
     *   total          = base + service + tax
     *
     * @return array{subtotal:int, discount:int, service_charge:int, tax:int, total:int}
     */
    public static function calculateTotals(int $subtotal, string $orderType, int $discount = 0): array
    {
        $discount = min($discount, $subtotal);
        $base = $subtotal - $discount;

        $service = $orderType === self::TYPE_DINE_IN
            ? (int) round($base * config('restaurant.service_rate'))
            : 0;

        $tax = (int) round(($base + $service) * config('restaurant.tax_rate'));

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'service_charge' => $service,
            'tax' => $tax,
            'total' => $base + $service + $tax,
        ];
    }

    /** Re-sum the order items and update the money columns. Call after adding/removing items. */
    public function recalculateTotals(): static
    {
        $subtotal = (int) $this->items()->sum('subtotal');

        $this->fill(self::calculateTotals($subtotal, $this->order_type, (int) $this->discount))->save();

        return $this;
    }

    /**
     * Generate a human-friendly, unique-per-day order number: ORD-20261029-0001
     */
    public static function generateOrderNumber(?Carbon $date = null): string
    {
        $date ??= now();
        $prefix = 'ORD-'.$date->format('Ymd').'-';

        $last = static::where('order_number', 'like', $prefix.'%')
            ->orderByDesc('order_number')
            ->value('order_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix.str_pad((string) $next, 4, '0', STR_PAD_LEFT);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    // ------------------------------------------------------------------
    // Accessors for the UI
    // ------------------------------------------------------------------

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->order_type] ?? $this->order_type;
    }

    public function getPaymentStatusLabelAttribute(): string
    {
        return self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status;
    }

    public function getFormattedTotalAttribute(): string
    {
        return config('restaurant.currency_prefix', 'Rp ').number_format($this->total, 0, ',', '.');
    }
}
