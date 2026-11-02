<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A single item on the menu (Nasi Goreng, Kopi Susu, ...).
 *
 * Category 1 --- * Menu
 * Menu     * --- * Order   (through order_items)
 *
 * Prices are stored as whole Rupiah (integer), Rupiah has no cents in practice
 * and integers avoid floating point rounding bugs.
 */
#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'image', 'is_available', 'is_featured'])]
class Menu extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * withTrashed(): a menu still shows its category name even if the
     * category was (soft) deleted later.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    /** Every order line that contains this menu. */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Many-to-many shortcut to the orders that contain this menu. */
    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class, 'order_items')
            ->withPivot(['quantity', 'unit_price', 'subtotal', 'notes'])
            ->withTimestamps();
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /** $menu->formatted_price  =>  "Rp 25.000" */
    public function getFormattedPriceAttribute(): string
    {
        return config('restaurant.currency_prefix', 'Rp ').number_format($this->price, 0, ',', '.');
    }

    /**
     * Resolves a menu image whether it comes from:
     * - External URL (https://...)
     * - Bundled public catalog asset (images/menus/*.webp)
     * - Staff form upload on the public storage disk (menus/*)
     */
    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (str_starts_with($this->image, 'images/')) {
            return asset($this->image);
        }

        return asset('storage/'.ltrim($this->image, '/'));
    }

    /** True only when $this->image points to an uploaded file on Storage::disk('public'). */
    public function hasStoredUpload(): bool
    {
        return (bool) $this->image
            && ! str_starts_with($this->image, 'http://')
            && ! str_starts_with($this->image, 'https://')
            && ! str_starts_with($this->image, 'images/');
    }
}
