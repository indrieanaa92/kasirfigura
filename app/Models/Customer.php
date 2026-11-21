<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'phone',
        'is_member',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_member' => 'boolean',
        ];
    }

    /**
     * Scope query to only customers marked as Member.
     */
    public function scopeMembers(Builder $query): Builder
    {
        return $query->where('is_member', true);
    }

    /**
     * Scope query to general customers (non-members).
     */
    public function scopeUmum(Builder $query): Builder
    {
        return $query->where('is_member', false);
    }

    /**
     * Transactions made by this customer by matching customer_name.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'customer_name', 'name');
    }
}
