<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductImage extends Model
{
    protected $fillable = [
        'product_id',
        'color_id',   
        'image',
        'alt_text',
        'sort_order',
    ];

    protected $casts = [
        'color_id'   => 'integer',
        'sort_order' => 'integer',
    ];


    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }


    public function scopeGeneral($query)
    {
        return $query->whereNull('color_id');
    }

    public function scopeForColor($query, int $colorId)
    {
        return $query->where('color_id', $colorId);
    }
}