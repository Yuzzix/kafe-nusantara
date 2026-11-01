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

    /** Resolve the currently selected shift staff from session, defaulting to Siti (cashier). */
    public static function currentShiftStaff(): ?self
    {
        $staffId = session('active_staff_id');
        if ($staffId) {
            $found = static::where('is_active', true)->find((int) $staffId);
            if ($found) {
                return $found;
            }
        }

        return static::where('is_active', true)->where('email', 'kasir@kafe.test')->first()
            ?? static::where('is_active', true)->first();
    }
}
