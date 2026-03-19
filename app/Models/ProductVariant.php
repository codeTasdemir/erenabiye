<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku',
        'price_modifier',
        'stock',
        'image',
        'is_active',
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(Size::class);
    }

    public function getFinalPriceAttribute(): float
    {
        return $this->product->price + $this->price_modifier;
    }

    public function getLabelAttribute(): string
    {
        $parts = [];
        if ($this->color) $parts[] = $this->color->name;
        if ($this->size)  $parts[] = $this->size->name;
        return implode(' / ', $parts);
    }
    public function getStockStatusAttribute(): string
    {
        return match (true) {
            $this->stock <= 0  => 'Tükendi',
            $this->stock <= 5  => 'Az Kaldı',
            default            => 'Stokta',
        };
    }
    public function getStockColorAttribute(): string
    {
        return match (true) {
            $this->stock <= 0  => 'danger',
            $this->stock <= 5  => 'warning',
            default            => 'success',
        };
    }
}
