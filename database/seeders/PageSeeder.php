<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            ['title' => 'Hakkımızda',           'slug' => 'hakkimizda'],
            ['title' => 'Müşteri Hizmetleri',   'slug' => 'musteri-hizmetleri'],
            ['title' => 'Kargo ve Teslimat',     'slug' => 'kargo-ve-teslimat'],
            ['title' => 'Yurtdışı Kargo',        'slug' => 'yurtdisi-kargo'],
            ['title' => 'İade Koşulları',        'slug' => 'iade-kosullari'],
            ['title' => 'Güvenli Alışveriş',     'slug' => 'guvenli-alisveris'],
            ['title' => 'Müşteri Memnuniyeti',   'slug' => 'musteri-memnuniyeti'],
            ['title' => 'Gizlilik İlkeleri',     'slug' => 'gizlilik-ilkeleri'],
            ['title' => 'Sıkça Sorulan Sorular', 'slug' => 'sss'],
            ['title' => 'KVKK',                  'slug' => 'kvkk'],
            ['title' => 'İletişim',              'slug' => 'iletisim'],
        ];

        foreach ($pages as $page) {
            Page::firstOrCreate(
                ['slug' => $page['slug']],
                [
                    'title'     => $page['title'],
                    'content'   => '<p>' . $page['title'] . ' sayfası içeriği buraya gelecek.</p>',
                    'is_active' => true,
                ]
            );
        }
    }
}