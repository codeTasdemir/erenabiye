<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'url',
        'type',
        'linkable_id',
        'target',
        'sort_order',
        'is_active',
    ];

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function getResolvedUrlAttribute(): string
    {
        if ($this->type === 'custom' || $this->url) {
            return $this->url ?? '#';
        }

        return match ($this->type) {
            'category' => $this->linkable_id
                ? route('category', Category::find($this->linkable_id)?->slug ?? '#')
                : '#',
            'page' => $this->linkable_id
                ? route('page', Page::find($this->linkable_id)?->slug ?? '#')
                : '#',
            'blog' => route('blog.index'),
            default => $this->url ?? '#',
        };
    }
}
