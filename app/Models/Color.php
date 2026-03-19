<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Color extends Model
{
    protected $fillable = ['name', 'hex_code', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
