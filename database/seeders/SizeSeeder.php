<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Size;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            ['name' => '34', 'label' => 'XS',  'sort_order' => 1],
            ['name' => '36', 'label' => 'S',   'sort_order' => 2],
            ['name' => '38', 'label' => 'M',   'sort_order' => 3],
            ['name' => '40', 'label' => 'L',   'sort_order' => 4],
            ['name' => '42', 'label' => 'XL',  'sort_order' => 5],
            ['name' => '44', 'label' => 'XXL', 'sort_order' => 6],
            ['name' => '46', 'label' => '3XL', 'sort_order' => 7],
            ['name' => '48', 'label' => '4XL', 'sort_order' => 8],
        ];

        foreach ($sizes as $size) {
            Size::updateOrCreate(['name' => $size['name']], $size);
        }
    }
}
