<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Color;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => 'Siyah',     'hex_code' => '#000000', 'sort_order' => 1],
            ['name' => 'Beyaz',     'hex_code' => '#FFFFFF', 'sort_order' => 2],
            ['name' => 'Kırmızı',   'hex_code' => '#FF0000', 'sort_order' => 3],
            ['name' => 'Lacivert',  'hex_code' => '#001F5B', 'sort_order' => 4],
            ['name' => 'Bordo',     'hex_code' => '#800020', 'sort_order' => 5],
            ['name' => 'Gümüş',    'hex_code' => '#C0C0C0', 'sort_order' => 6],
            ['name' => 'Altın',     'hex_code' => '#FFD700', 'sort_order' => 7],
            ['name' => 'Pembe',     'hex_code' => '#FFC0CB', 'sort_order' => 8],
            ['name' => 'Lila',      'hex_code' => '#C8A2C8', 'sort_order' => 9],
            ['name' => 'Yeşil',    'hex_code' => '#008000', 'sort_order' => 10],
            ['name' => 'Mavi',      'hex_code' => '#0000FF', 'sort_order' => 11],
            ['name' => 'Pudra',     'hex_code' => '#F4C2C2', 'sort_order' => 12],
            ['name' => 'Ekru',      'hex_code' => '#C2B280', 'sort_order' => 13],
            ['name' => 'Şampanya', 'hex_code' => '#F7E7CE', 'sort_order' => 14],
        ];

        foreach ($colors as $color) {
            Color::updateOrCreate(['name' => $color['name']], $color);
        }
    }
}
