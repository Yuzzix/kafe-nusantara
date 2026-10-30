<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A physical table in the restaurant (named DiningTable because "Table"
 * is confusing next to database tables).
 *
 * DiningTable 1 --- * Order   (only dine-in orders have a table)
 */
#[Fillable(['number', 'capacity', 'area', 'status'])]
class DiningTable extends Model
{
    public const STATUS_AVAILABLE = 'available';
    public const STATUS_OCCUPIED = 'occupied';
    public const STATUS_RESERVED = 'reserved';

    public const STATUSES = [
        self::STATUS_AVAILABLE => 'Tersedia',
        self::STATUS_OCCUPIED => 'Terisi',
        self::STATUS_RESERVED => 'Dipesan',
    ];

    public const AREAS = ['Indoor', 'Outdoor', 'VIP'];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Orders still running at this table (not completed / cancelled). */
    public function activeOrders(): HasMany
    {
        return $this->hasMany(Order::class)->whereIn('status', Order::ACTIVE_STATUSES);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }
}
