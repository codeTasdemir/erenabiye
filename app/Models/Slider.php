<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
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
                        ->toWebp(40)
                        ->save($newPath);

                    unlink($sourcePath);
                    $model->image = $newFilename;
                }
            }
        });
    }

    protected $fillable = [
        'title',
        'subtitle',
        'image',
        'mobile_image',
        'button_text',
        'button_url',
        'sort_order',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
