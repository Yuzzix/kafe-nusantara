<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Menu category (Makanan Utama, Kopi, Dessert, ...).
 *
 * Category 1 --- * Menu
 */
#[Fillable(['name', 'slug', 'description', 'icon', 'sort_order', 'is_active'])]
class Category extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /** All menus in this category. */
    public function menus(): HasMany
    {
        return $this->hasMany(Menu::class);
    }

    /** Only menus that can currently be ordered. */
    public function availableMenus(): HasMany
    {
        return $this->hasMany(Menu::class)->where('is_available', true);
    }

    /** Category::active()->ordered()->get() */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
