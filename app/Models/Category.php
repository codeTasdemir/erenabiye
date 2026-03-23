<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships as EloquentHasRecursiveRelationships;

class Category extends Model
{
    use EloquentHasRecursiveRelationships, HasSlug;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'sort_order',
        'is_active',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->slugsShouldBeNoLongerThan(50);
    }

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getParentKeyName(): string
    {
        return 'parent_id';
    }



    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
    public function allProducts()
    {
        return Product::whereIn(
            'category_id',
            $this->descendantsAndSelf()->pluck('id')
        );
    }
    public function getFullPathAttribute(): string
    {
        return $this->ancestorsAndSelf()
            ->pluck('name')
            ->implode(' > ');
    }
}
