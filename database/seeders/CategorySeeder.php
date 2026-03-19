<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ana Kategoriler
        $anaKategoriler = [
            ['name' => 'Uzun Abiye',  'sort_order' => 1],
            ['name' => 'Kısa Abiye', 'sort_order' => 2],
            ['name' => 'Fırsat',     'sort_order' => 3],
        ];

        foreach ($anaKategoriler as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['name']),
                    'parent_id'  => null,
                    'is_active'  => true,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }

        // Alt Kategoriler — Uzun Abiye
        $uzunAbiye = Category::where('slug', 'uzun-abiye')->first();

        $uzunAlt = [
            ['name' => 'Balık Abiye',     'sort_order' => 1],
            ['name' => 'Prenses Abiye',   'sort_order' => 2],
            ['name' => 'Askılı Abiye',    'sort_order' => 3],
            ['name' => 'Transparan Abiye', 'sort_order' => 4],
            ['name' => 'Kloş Abiye',      'sort_order' => 5],
        ];

        foreach ($uzunAlt as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['name']),
                    'parent_id'  => $uzunAbiye->id,
                    'is_active'  => true,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }

        // Alt Kategoriler — Kısa Abiye
        $kisaAbiye = Category::where('slug', 'kisa-abiye')->first();

        $kisaAlt = [
            ['name' => 'Mini Abiye',     'sort_order' => 1],
            ['name' => 'Midi Abiye',     'sort_order' => 2],
            ['name' => 'Kokteyl Elbise', 'sort_order' => 3],
        ];

        foreach ($kisaAlt as $data) {
            Category::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name'       => $data['name'],
                    'slug'       => Str::slug($data['name']),
                    'parent_id'  => $kisaAbiye->id,
                    'is_active'  => true,
                    'sort_order' => $data['sort_order'],
                ]
            );
        }
    }
}
