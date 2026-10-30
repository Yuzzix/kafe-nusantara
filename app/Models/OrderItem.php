<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One line of an order: "2x Kopi Susu Gula Aren @ Rp 25.000, less sugar".
 *
 * This is the pivot between Order and Menu, but it is a full model because it
 * carries important data of its own:
 *   - unit_price: price SNAPSHOT at the moment of ordering. If the menu price
 *     changes next month, old orders/reports must not change.
 *   - notes: per-item requests (less sugar, no spicy, ...).
 */
#[Fillable(['order_id', 'menu_id', 'quantity', 'unit_price', 'subtotal', 'notes'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_price' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /** withTrashed(): old orders still show the menu even after it is deleted. */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class)->withTrashed();
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return config('restaurant.currency_prefix', 'Rp ').number_format($this->subtotal, 0, ',', '.');
    }
}
