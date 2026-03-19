<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlogPost extends Model
{
    protected static function booted(): void
    {
        static::saving(function ($model) {
            if ($model->isDirty('image') && $model->image) {
                $sourcePath = storage_path('app/public/' . $model->image);

                if (file_exists($sourcePath)) {
                    $manager = new \Intervention\Image\ImageManager(
                        new \Intervention\Image\Drivers\Gd\Driver()
                    );

                    $newFilename = pathinfo($model->image, PATHINFO_DIRNAME)
                        . '/' . uniqid() . '.webp';
                    $newPath = storage_path('app/public/' . $newFilename);

                    $manager->read($sourcePath)
                        ->toWebp(85)
                        ->save($newPath);

                    unlink($sourcePath);
                    $model->image = $newFilename;
                }
            }
        });
    }

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'image',
        'is_published',
        'published_at',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'is_published' => 'boolean',
    ];
}
