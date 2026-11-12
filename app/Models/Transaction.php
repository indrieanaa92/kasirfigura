<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    public const PAYMENT_CASH = 'cash';
    public const PAYMENT_QRIS = 'qris';
    public const PAYMENT_TRANSFER = 'transfer';
    public const PAYMENT_DEBIT = 'debit';

    public const PAYMENT_METHODS = [
        self::PAYMENT_CASH => 'Cash (Tunai)',
        self::PAYMENT_QRIS => 'QRIS',
        self::PAYMENT_TRANSFER => 'Transfer Bank',
        self::PAYMENT_DEBIT => 'Kartu Debit / EDC',
    ];

    public const CUSTOMER_SALES = 'sales';
    public const CUSTOMER_AGEN = 'agen';
    public const CUSTOMER_UMUM = 'umum';

    public const CUSTOMER_TYPES = [
        self::CUSTOMER_SALES => 'Sales',
        self::CUSTOMER_AGEN => 'Agen',
        self::CUSTOMER_UMUM => 'Umum',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_name',
        'customer_type',
        'total_amount',
        'payment_method',
        'paid_amount',
        'change_amount',
    ];

    /**
     * The accessors to append to the model's array/JSON form.
     *
     * @var list<string>
     */
    protected $appends = [
        'payment_method_label',
        'customer_type_label',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    /**
     * Human-readable label for the transaction payment method.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return self::PAYMENT_METHODS[$this->payment_method] ?? ucfirst((string) $this->payment_method);
    }

    /**
     * Human-readable label for the buyer tier/status.
     */
    public function getCustomerTypeLabelAttribute(): string
    {
        $type = $this->customer_type ?: self::CUSTOMER_UMUM;

        return self::CUSTOMER_TYPES[$type] ?? ucfirst((string) $type);
    }

    /**
     * Get the kasir user who created this transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all line items belonging to this transaction.
     */
    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }
}
