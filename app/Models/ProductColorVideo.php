<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColorVideo extends Model
{
    protected $fillable = ['product_id', 'color_id', 'video_url'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(Color::class);
    }

    public function getVideoIdAttribute(): ?string
    {
        preg_match(
            '/(?:shorts\/|youtu\.be\/|watch\?v=|embed\/)([a-zA-Z0-9_-]{11})/',
            $this->video_url,
            $matches
        );

        return $matches[1] ?? null;
    }
}