<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A staff member of the restaurant (admin, cashier, waiter, kitchen).
 * Customers are NOT users; their name is stored directly on the order.
 */
#[Fillable(['name', 'email', 'password', 'role', 'phone', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_CASHIER = 'cashier';

    public const ROLE_WAITER = 'waiter';

    public const ROLE_KITCHEN = 'kitchen';

    /** Value => label, handy for <select> dropdowns in Blade. */
    public const ROLES = [
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_CASHIER => 'Kasir',
        self::ROLE_WAITER => 'Pelayan',
        self::ROLE_KITCHEN => 'Dapur',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** Orders that this staff member created / handled. */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Ringkasan Gerai (Admin & Kasir). */
    public function canViewDashboard(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_CASHIER], true);
    }

    /** Multi-day revenue analytics & CSV financial export (Admin & Kasir for CSV, Admin for 30-day chart). */
    public function canViewManagerAnalytics(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** Full CRUD on Categories & Menu pricing/items (Admin only). */
    public function canManageCatalog(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /** View Daftar Menu & toggle daily stock Tersedia/Habis (Admin, Kasir, Dapur). */
    public function canViewMenuCatalog(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_CASHIER, self::ROLE_KITCHEN], true);
    }

    /** Create & view customer orders (Admin, Kasir, Pelayan). */
    public function canAccessOrders(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_CASHIER, self::ROLE_WAITER], true);
    }

    /** Accept cash/QRIS/card payments, print receipts, and export CSV (Admin & Kasir). */
    public function canProcessPayment(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_CASHIER], true);
    }

    /** View & update dining table statuses (Admin, Kasir, Pelayan). */
    public function canManageTables(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_CASHIER, self::ROLE_WAITER], true);
    }

    /** Access Layar Dapur / Kitchen Display System (Admin & Dapur). */
    public function canViewKitchen(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_KITCHEN], true);
    }

    /** Primary landing route when switching into this staff member's shift. */
    public function defaultRouteName(): string
    {
        return match ($this->role) {
            self::ROLE_KITCHEN => 'kitchen.index',
            self::ROLE_WAITER => 'tables.index',
            default => 'dashboard',
        };
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }

    public function getInitialsAttribute(): string
    {
        $words = preg_split('/\s+/', trim((string) $this->name)) ?: [];
        $initials = collect($words)
            ->filter()
            ->take(2)
            ->map(fn (string $word) => mb_strtoupper(mb_substr($word, 0, 1)))
            ->join('');

        return $initials !== '' ? $initials : 'KN';
    }

    /** Resolve the currently selected shift staff from session, defaulting to Admin so fresh loads unlock everything. */
    public static function currentShiftStaff(): ?self
    {
        $staffId = session('active_staff_id');
        if ($staffId) {
            $found = static::where('is_active', true)->find((int) $staffId);
            if ($found) {
                return $found;
            }
        }

        return static::where('is_active', true)->where('role', self::ROLE_ADMIN)->first()
            ?? static::where('is_active', true)->first();
    }
}
