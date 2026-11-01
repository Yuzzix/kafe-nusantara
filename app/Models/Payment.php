<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A payment for an order. Order 1 --- * Payment, which allows split bills
 * (e.g. half cash, half QRIS). Normally an order has exactly one payment.
 */
#[Fillable(['order_id', 'method', 'amount', 'received_amount', 'change_amount', 'reference_number', 'paid_at'])]
class Payment extends Model
{
    public const METHOD_CASH = 'cash';

    public const METHOD_QRIS = 'qris';

    public const METHOD_DEBIT = 'debit_card';

    public const METHOD_CREDIT = 'credit_card';

    public const METHOD_EWALLET = 'e_wallet';

    public const METHODS = [
        self::METHOD_CASH => 'Tunai',
        self::METHOD_QRIS => 'QRIS',
        self::METHOD_DEBIT => 'Kartu Debit',
        self::METHOD_CREDIT => 'Kartu Kredit',
        self::METHOD_EWALLET => 'E-Wallet (GoPay/OVO/Dana)',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'received_amount' => 'integer',
            'change_amount' => 'integer',
            'paid_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }
}
