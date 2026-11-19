<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'unit',
        'stock_quantity',
        'image_url',
        'emoji',
        'gradient',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price'          => 'integer',
            'stock_quantity' => 'integer',
            'is_active'      => 'boolean',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->stock_quantity > 0;
    }
}
