<?php

namespace App\Services;

use App\Models\Setting;

class SeoService
{
    public static function defaults(): array
    {
        return [
            'title'       => Setting::get('meta_title', 'Eren Abiye — Özel Tasarım Abiye Modelleri'),
            'description' => Setting::get('meta_description', 'En şık abiye modelleri, özel tasarımlar ve uygun fiyatlarla Eren Abiye\'de.'),
            'keywords'    => Setting::get('meta_keywords', 'abiye, uzun abiye, kısa abiye, özel tasarım abiye'),
            'image'       => Setting::get('seo_image', asset('images/og-default.jpg')),
            'url'         => url()->current(),
            'type'        => 'website',
            'locale'      => 'tr_TR',
            'site_name'   => Setting::get('site_name', 'Eren Abiye'),
        ];
    }

    public static function forProduct(\App\Models\Product $product): array
    {
        return [
            'title'       => ($product->meta_title ?: $product->name) . ' — Eren Abiye',
            'description' => $product->meta_description
                ?: ($product->short_description ?: substr(strip_tags($product->description ?? ''), 0, 160)),
            'keywords'    => $product->meta_keywords
                ?: $product->name . ', abiye, ' . $product->category?->name,
            'image'       => $product->main_image
                ? asset('storage/' . $product->main_image)
                : asset('images/og-default.jpg'),
            'url'         => route('product', $product->slug),
            'type'        => 'product',
            'locale'      => 'tr_TR',
            'site_name'   => Setting::get('site_name', 'Eren Abiye'),
        ];
    }

    public static function forCategory(\App\Models\Category $category): array
    {
        return [
            'title'       => ($category->meta_title ?: $category->name) . ' — Eren Abiye',
            'description' => $category->meta_description
                ?: $category->name . ' modelleri en uygun fiyatlarla Eren Abiye\'de.',
            'keywords'    => $category->meta_keywords
                ?: $category->name . ', abiye, özel tasarım',
            'image'       => $category->image
                ? asset('storage/' . $category->image)
                : asset('images/og-default.jpg'),
            'url'         => route('category', $category->slug),
            'type'        => 'website',
            'locale'      => 'tr_TR',
            'site_name'   => Setting::get('site_name', 'Eren Abiye'),
        ];
    }
}