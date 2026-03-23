<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use App\Models\ProductColorVideo;
use Illuminate\Database\Eloquent\Builder;


class Product extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_price',
        'cost_price',
        'stock',
        'low_stock_alert',
        'main_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_featured',
        'is_new',
        'is_weekly_product',
        'track_stock',
        'sort_order',
        'views',
    ];

    protected $casts = [
        'price'            => 'decimal:2',
        'compare_price'    => 'decimal:2',
        'cost_price'       => 'decimal:2',
        'is_active'        => 'boolean',
        'is_featured'      => 'boolean',
        'is_new'           => 'boolean',
        'is_weekly_product' => 'boolean',
        'track_stock'      => 'boolean',
    ];


    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function generalImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->whereNull('color_id')
            ->orderBy('sort_order');
    }

    public function colorImages(): HasMany
    {
        return $this->hasMany(ProductImage::class)
            ->whereNotNull('color_id')
            ->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }


    public function imagesForColor(int $colorId): \Illuminate\Support\Collection
    {
        return $this->images()
            ->where('color_id', $colorId)
            ->orderBy('sort_order')
            ->get();
    }

    public function galleryImagesForColor(int $colorId): \Illuminate\Support\Collection
    {
        $colorImages   = $this->imagesForColor($colorId);
        $generalImages = $this->images()->whereNull('color_id')->orderBy('sort_order')->get();

        return $colorImages->isNotEmpty()
            ? $colorImages->concat($generalImages)
            : $generalImages;
    }


    public function defaultGalleryImages(): \Illuminate\Support\Collection
    {
        return $this->images()->whereNull('color_id')->orderBy('sort_order')->get();
    }


    public function getDiscountPercentageAttribute(): ?int
    {
        if ($this->compare_price && $this->compare_price > $this->price) {
            return (int) round((($this->compare_price - $this->price) / $this->compare_price) * 100);
        }
        return null;
    }

    public function getTotalStockAttribute(): int
    {
        if ($this->variants()->exists()) {
            return $this->variants()->where('is_active', true)->sum('stock');
        }
        return $this->stock;
    }

    public function getAvailableSizesForColor(int $colorId): \Illuminate\Support\Collection
    {
        return $this->variants()
            ->where('color_id', $colorId)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->with('size')
            ->get()
            ->pluck('size');
    }

    public function getAvailableColorsAttribute(): \Illuminate\Support\Collection
    {
        return $this->variants()
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->with('color')
            ->get()
            ->pluck('color')
            ->unique('id');
    }


    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(80);
    }

    public function colorVideos(): HasMany
    {
        return $this->hasMany(ProductColorVideo::class);
    }
    public function scopeLowStock(Builder $query): Builder
    {
        return $query
            ->where('track_stock', true)
            ->withCount('variants')
            ->withSum('variants', 'stock')
            ->whereRaw('
            CASE 
                WHEN (SELECT COUNT(*) FROM product_variants WHERE product_id = products.id AND is_active = true) > 0 
                THEN (SELECT COALESCE(SUM(stock), 0) FROM product_variants WHERE product_id = products.id AND is_active = true)
                ELSE products.stock 
            END <= products.low_stock_alert
        ');
    }
}
