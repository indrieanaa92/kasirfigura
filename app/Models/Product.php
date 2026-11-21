<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'size',
        'price',
        'price_member',
        'price_umum',
        'price_sales',
        'price_agen',
        'stock',
    ];

    /**
     * Ensure tier prices and legacy base price stay synchronized on save.
     */
    protected static function booted(): void
    {
        static::saving(function (Product $product): void {
            if ($product->price_umum === null && $product->price !== null) {
                $product->price_umum = $product->price;
            }
            if ($product->price_member === null && $product->price !== null) {
                $product->price_member = $product->price_sales !== null ? $product->price_sales : round(((float) $product->price) * 0.85);
            }
            if ($product->price_umum !== null) {
                $product->price = $product->price_umum;
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'price_member' => 'decimal:2',
            'price_umum' => 'decimal:2',
            'price_sales' => 'decimal:2',
            'price_agen' => 'decimal:2',
            'stock' => 'integer',
        ];
    }

    /**
     * Resolve unit price based on buyer status (member vs umum).
     */
    public function getPriceForCustomerType(string $customerType): float
    {
        $type = strtolower(trim($customerType));

        if ($type === Transaction::CUSTOMER_MEMBER || $type === 'sales' || $type === 'agen') {
            return (float) ($this->price_member ?? $this->price_sales ?? round(((float) $this->price) * 0.85));
        }

        return (float) ($this->price_umum ?? $this->price);
    }

    /**
     * Scope query to only include frames that are currently in stock.
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    /**
     * Get all transaction detail records referencing this frame product.
     */
    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class, 'product_id');
    }
}

