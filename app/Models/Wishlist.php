<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $fillable = ['user_id', 'session_id', 'product_id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public static function forCurrentUser(): \Illuminate\Database\Eloquent\Builder
    {
        if (auth()->check()) {
            return static::where('user_id', auth()->id());
        }

        return static::where('session_id', session()->getId());
    }

    public static function hasProduct(int $productId): bool
    {
        return static::forCurrentUser()
            ->where('product_id', $productId)
            ->exists();
    }
}