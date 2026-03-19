<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        // Header Menüsü
        $header = Menu::create([
            'name'      => 'Ana Menü',
            'location'  => 'header',
            'is_active' => true,
        ]);

        $items = [
            ['label' => 'Ana Sayfa',    'url' => '/',           'sort_order' => 1],
            ['label' => 'Uzun Abiye',   'url' => '/kategori/uzun-abiye',  'sort_order' => 2],
            ['label' => 'Kısa Abiye',   'url' => '/kategori/kisa-abiye',  'sort_order' => 3],
            ['label' => 'Fırsatlar',    'url' => '/kategori/firsat',      'sort_order' => 4],
            ['label' => 'Blog',         'url' => '/blog',       'sort_order' => 5],
            ['label' => 'İletişim',     'url' => '/iletisim',   'sort_order' => 6],
        ];

        foreach ($items as $item) {
            MenuItem::create(array_merge($item, [
                'menu_id'   => $header->id,
                'type'      => 'custom',
                'target'    => '_self',
                'is_active' => true,
            ]));
        }

        // KATEGORİLER Menüsü
        $categories = Menu::create([
            'name'      => 'Kategoriler',
            'location'  => 'categories',
            'is_active' => true,
        ]);

        foreach (
            [
                ['label' => 'Abiye Elbise',      'url' => '/kategori/abiye-elbise',    'sort_order' => 1],
                ['label' => 'Çocuk Abiye',       'url' => '/kategori/cocuk-abiye',     'sort_order' => 2],
                ['label' => 'Ayakkabı',          'url' => '/kategori/ayakkabi',        'sort_order' => 3],
                ['label' => 'Çanta',             'url' => '/kategori/canta',           'sort_order' => 4],
                ['label' => 'Taki',              'url' => '/kategori/taki',            'sort_order' => 5],
                ['label' => 'Fırsat Ürünleri',   'url' => '/kategori/firsat-urunleri', 'sort_order' => 6],
            ] as $item
        ) {
            MenuItem::create(array_merge($item, [
                'menu_id'   => $categories->id,
                'type'      => 'custom',
                'target'    => '_self',
                'is_active' => true,
            ]));
        }

        // ABİYE KATEGORİLERİ Menüsü
        $abiyeCategories = Menu::create([
            'name'      => 'Abiye Kategorileri',
            'location'  => 'abiye_categories',
            'is_active' => true,
        ]);

        foreach (
            [
                ['label' => 'Yeni Sezon Abiyeler',   'url' => '/kategori/yeni-sezon',       'sort_order' => 1],
                ['label' => 'Exclusive Series',      'url' => '/kategori/exclusive',        'sort_order' => 2],
                ['label' => 'Mezuniyet Elbisesi',    'url' => '/kategori/mezuniyet',       'sort_order' => 3],
                ['label' => 'Nişan Elbisesi',        'url' => '/kategori/nisan',           'sort_order' => 4],
                ['label' => 'Davet & Gece Elbisesi', 'url' => '/kategori/davet-gece',      'sort_order' => 5],
                ['label' => 'Büyük Beden Abiye',     'url' => '/kategori/buyuk-beden',     'sort_order' => 6],
                ['label' => 'Kısa Abiye Modelleri',  'url' => '/kategori/kisa-abiye-model', 'sort_order' => 7],
                ['label' => 'Uzun Abiye Modelleri',  'url' => '/kategori/uzun-abiye-model', 'sort_order' => 8],
                ['label' => 'Balık Abiye Modelleri', 'url' => '/kategori/balik-abiye',      'sort_order' => 9],
                ['label' => 'Hamile Abiye Modelleri','url' => '/kategori/hamile-abiye',    'sort_order' => 10],
            ] as $item
        ) {
            MenuItem::create(array_merge($item, [
                'menu_id'   => $abiyeCategories->id,
                'type'      => 'custom',
                'target'    => '_self',
                'is_active' => true,
            ]));
        }

        // Footer — Kurumsal
        $footer1 = Menu::create([
            'name'      => 'Kurumsal',
            'location'  => 'footer_1',
            'is_active' => true,
        ]);

        foreach (
            [
                ['label' => 'Hakkımızda',              'url' => '/hakkimizda',              'sort_order' => 1],
                ['label' => 'Müşteri Hizmetleri',      'url' => '/musteri-hizmetleri',      'sort_order' => 2],
                ['label' => 'Kargo ve Teslimat',       'url' => '/kargo-teslimat',          'sort_order' => 3],
                ['label' => 'Yurtdışı Kargo',          'url' => '/yurtdisi-kargo',          'sort_order' => 4],
                ['label' => 'İade Koşulları',          'url' => '/iade-kosullari',          'sort_order' => 5],
                ['label' => 'Güvenli Alışveriş',       'url' => '/guvenli-alisveris',       'sort_order' => 6],
                ['label' => '%100 Müşteri Memnuniyeti','url' => '/musteri-memnuniyeti',     'sort_order' => 7],
                ['label' => 'Gizlilik İlkeleri',       'url' => '/gizlilik-ilkeleri',       'sort_order' => 8],
                ['label' => 'Sıkça Sorulan Sorular',   'url' => '/sikcasi-sorulan-sorular', 'sort_order' => 9],
                ['label' => 'KVKK Aydınlatma Metni',   'url' => '/kvkk-aydinlatma',         'sort_order' => 10],
            ] as $item
        ) {
            MenuItem::create(array_merge($item, [
                'menu_id'   => $footer1->id,
                'type'      => 'custom',
                'target'    => '_self',
                'is_active' => true,
            ]));
        }

        // Footer — Yardım / Hızlı Erişim
        $footer2 = Menu::create([
            'name'      => 'Hızlı Erişim',
            'location'  => 'footer_2',
            'is_active' => true,
        ]);

        foreach (
            [
                ['label' => 'Giriş Yap',       'url' => '/giris',          'sort_order' => 1],
                ['label' => 'Üye Ol',         'url' => '/kayit',          'sort_order' => 2],
                ['label' => 'İletişim',       'url' => '/iletisim',       'sort_order' => 3],
                ['label' => 'Kargo Takibi',   'url' => '/kargo-takibi',   'sort_order' => 4],
            ] as $item
        ) {
            MenuItem::create(array_merge($item, [
                'menu_id'   => $footer2->id,
                'type'      => 'custom',
                'target'    => '_self',
                'is_active' => true,
            ]));
        }

        // Footer — Kategoriler
        $footer3 = Menu::create([
            'name'      => 'Kategoriler',
            'location'  => 'footer_3',
            'is_active' => true,
        ]);

        foreach (
            [
                ['label' => 'Uzun Abiye',  'url' => '/kategori/uzun-abiye', 'sort_order' => 1],
                ['label' => 'Kısa Abiye',  'url' => '/kategori/kisa-abiye', 'sort_order' => 2],
                ['label' => 'Balık Abiye', 'url' => '/kategori/balik-abiye', 'sort_order' => 3],
                ['label' => 'Prenses',     'url' => '/kategori/prenses',    'sort_order' => 4],
                ['label' => 'Fırsatlar',   'url' => '/kategori/firsat',     'sort_order' => 5],
            ] as $item
        ) {
            MenuItem::create(array_merge($item, [
                'menu_id'   => $footer3->id,
                'type'      => 'custom',
                'target'    => '_self',
                'is_active' => true,
            ]));
        }
    }
}