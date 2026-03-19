<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Category;
use App\Models\Color;
use App\Models\Size;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // Kategoriler
        $balik   = Category::where('slug', 'balik-abiye')->first();
        $prenses = Category::where('slug', 'prenses-abiye')->first();
        $askili  = Category::where('slug', 'askili-abiye')->first();

        // Renkler
        $siyah    = Color::where('name', 'Siyah')->first();
        $kirmizi  = Color::where('name', 'Kırmızı')->first();
        $lacivert = Color::where('name', 'Lacivert')->first();
        $bordo    = Color::where('name', 'Bordo')->first();
        $beyaz    = Color::where('name', 'Beyaz')->first();
        $altin    = Color::where('name', 'Altın')->first();
        $gumus    = Color::where('name', 'Gümüş')->first();
        $pudra    = Color::where('name', 'Pudra')->first();
        $yesil    = Color::where('name', 'Yeşil')->first();
        $mor      = Color::where('name', 'Mor')->first();

        $bedenler = Size::all();

        $urunler = [

            // ── BALIK ABİYE (40 ürün) ──────────────────────────────────────────
            [
                'category' => $balik, 'name' => 'Kalp Yaka Nakış İşlemeli Balık Abiye',
                'price' => 7899.00, 'compare_price' => 9500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $kirmizi, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'Straplez Boncuk İşlemeli Balık Elbise',
                'price' => 4999.00, 'compare_price' => 6500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $lacivert],
            ],
            [
                'category' => $balik, 'name' => 'Derin V Yaka Payetli Balık Abiye',
                'price' => 6299.00, 'compare_price' => 7800.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $altin, $gumus],
            ],
            [
                'category' => $balik, 'name' => 'Omuz Dekolteli Dantel Balık Abiye',
                'price' => 5499.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $kirmizi, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'Uzun Kollu Taş İşlemeli Balık Abiye',
                'price' => 8499.00, 'compare_price' => 10200.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $lacivert, $mor],
            ],
            [
                'category' => $balik, 'name' => 'Madonna Yaka Simli Balık Abiye',
                'price' => 5299.00, 'compare_price' => 6400.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$altin, $gumus, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Asymmetrik Omuz Tül Balık Abiye',
                'price' => 6799.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$pudra, $beyaz, $lacivert],
            ],
            [
                'category' => $balik, 'name' => 'İnce Askılı Saten Balık Abiye',
                'price' => 4299.00, 'compare_price' => 5500.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$siyah, $kirmizi, $pudra],
            ],
            [
                'category' => $balik, 'name' => 'Yırtmaçlı Kadife Balık Abiye',
                'price' => 5799.00, 'compare_price' => 7200.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$bordo, $siyah, $yesil],
            ],
            [
                'category' => $balik, 'name' => 'Balık Yaka Pul Payetli Balık Abiye',
                'price' => 7199.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Halter Yaka Boncuklu Balık Abiye',
                'price' => 6099.00, 'compare_price' => 7500.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$lacivert, $siyah, $kirmizi],
            ],
            [
                'category' => $balik, 'name' => 'Transparan Detaylı Balık Abiye',
                'price' => 8999.00, 'compare_price' => 11000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'Japon Yaka Çiçek Baskılı Balık Abiye',
                'price' => 4799.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $yesil],
            ],
            [
                'category' => $balik, 'name' => 'Tek Omuz Kristal Taşlı Balık Abiye',
                'price' => 9299.00, 'compare_price' => 11500.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Önden Yırtmaçlı Şifon Balık Abiye',
                'price' => 5199.00, 'compare_price' => 6200.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$kirmizi, $siyah, $lacivert],
            ],
            [
                'category' => $balik, 'name' => 'Düşük Sırt Dekolte Balık Abiye',
                'price' => 6599.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $bordo, $mor],
            ],
            [
                'category' => $balik, 'name' => 'Kolsuz Dantel Kaplama Balık Abiye',
                'price' => 5899.00, 'compare_price' => 7100.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Ön Büzgülü Volan Detaylı Balık Abiye',
                'price' => 4699.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$kirmizi, $bordo, $lacivert],
            ],
            [
                'category' => $balik, 'name' => 'Sırt Dekolteli Bağcıklı Balık Abiye',
                'price' => 7499.00, 'compare_price' => 9000.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $altin, $gumus],
            ],
            [
                'category' => $balik, 'name' => 'El Nakışlı Klasik Balık Abiye',
                'price' => 10999.00, 'compare_price' => 13500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $lacivert, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'V Yaka Simli Kumaş Balık Abiye',
                'price' => 5599.00, 'compare_price' => 6800.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Straplez Kadife Kumaş Balık Abiye',
                'price' => 6199.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$bordo, $siyah, $mor],
            ],
            [
                'category' => $balik, 'name' => 'Kare Yaka Tül Etekli Balık Abiye',
                'price' => 7299.00, 'compare_price' => 8800.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$pudra, $beyaz, $lacivert],
            ],
            [
                'category' => $balik, 'name' => 'Omuz Detaylı Organze Balık Abiye',
                'price' => 8199.00, 'compare_price' => 9900.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $kirmizi, $gumus],
            ],
            [
                'category' => $balik, 'name' => 'Çan Kollu Güpür Dantelı Balık Abiye',
                'price' => 6899.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Boyun Bağlamalı Saten Balık Abiye',
                'price' => 4899.00, 'compare_price' => 6100.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$kirmizi, $siyah, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'Pelerin Detaylı Boncuk İşlemeli Balık Abiye',
                'price' => 9499.00, 'compare_price' => 11800.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $lacivert, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'Kristal Kemeli Tasarım Balık Abiye',
                'price' => 11299.00, 'compare_price' => 14000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$gumus, $altin],
            ],
            [
                'category' => $balik, 'name' => 'Yuvarlak Yaka Payetli Balık Abiye',
                'price' => 5999.00, 'compare_price' => 7400.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$siyah, $altin, $gumus],
            ],
            [
                'category' => $balik, 'name' => 'Etek Ucu Fırfırlı Derin Yaka Balık Abiye',
                'price' => 6499.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$kirmizi, $bordo, $pudra],
            ],
            [
                'category' => $balik, 'name' => 'Tüy Detaylı Glamour Balık Abiye',
                'price' => 12999.00, 'compare_price' => 16000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $beyaz, $pudra],
            ],
            [
                'category' => $balik, 'name' => 'Mercan İşlemeli Bohem Balık Abiye',
                'price' => 7799.00, 'compare_price' => 9300.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$lacivert, $bordo, $yesil],
            ],
            [
                'category' => $balik, 'name' => 'Yılan Derisi Baskılı Saten Balık Abiye',
                'price' => 5399.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$siyah, $beyaz, $altin],
            ],
            [
                'category' => $balik, 'name' => 'Volanlı Etek Şifon Balık Abiye',
                'price' => 6299.00, 'compare_price' => 7700.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$pudra, $beyaz, $lacivert],
            ],
            [
                'category' => $balik, 'name' => 'Mini Gelinlik Usulü Balık Abiye',
                'price' => 8699.00, 'compare_price' => 10500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $pudra],
            ],
            [
                'category' => $balik, 'name' => 'Parlak Kumaş Straplez Balık Abiye',
                'price' => 4599.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $balik, 'name' => 'Korseli Dantelli Klasik Balık Abiye',
                'price' => 7099.00, 'compare_price' => 8600.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $kirmizi, $bordo],
            ],
            [
                'category' => $balik, 'name' => 'Ön Açık Tasarım Lüks Balık Abiye',
                'price' => 9999.00, 'compare_price' => 12500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $lacivert, $gumus],
            ],
            [
                'category' => $balik, 'name' => 'Taş Nakışlı Uzun Kollu Balık Abiye',
                'price' => 8299.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$bordo, $lacivert, $mor],
            ],
            [
                'category' => $balik, 'name' => 'Degrade Renk Geçişli Balık Abiye',
                'price' => 6799.00, 'compare_price' => 8200.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$mor, $lacivert, $kirmizi],
            ],

            // ── PRENSES ABİYE (35 ürün) ───────────────────────────────────────
            [
                'category' => $prenses, 'name' => 'Straplez Çiçek Desenli Pelerin Prenses Abiye',
                'price' => 8159.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$bordo, $lacivert, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Kabarık Etekli Kristal Taşlı Prenses Abiye',
                'price' => 11499.00, 'compare_price' => 14000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $lacivert],
            ],
            [
                'category' => $prenses, 'name' => 'Tül Etekli Korse Prenses Abiye',
                'price' => 9299.00, 'compare_price' => 11500.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Nakış İşlemeli Büyük Prenses Abiye',
                'price' => 13499.00, 'compare_price' => 16500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $altin],
            ],
            [
                'category' => $prenses, 'name' => 'Kısa Ön Uzun Arka Prenses Abiye',
                'price' => 7499.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$pudra, $lacivert, $mor],
            ],
            [
                'category' => $prenses, 'name' => 'Üç Boyutlu Çiçek Aplikeli Prenses Abiye',
                'price' => 10299.00, 'compare_price' => 12800.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Kuyruklu Tafta Prenses Abiye',
                'price' => 14999.00, 'compare_price' => 18000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $lacivert, $bordo],
            ],
            [
                'category' => $prenses, 'name' => 'Boncuk İşlemeli Kabarık Prenses Abiye',
                'price' => 8799.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$pudra, $beyaz, $gumus],
            ],
            [
                'category' => $prenses, 'name' => 'Omuz Fırfırlı Şifon Prenses Abiye',
                'price' => 7299.00, 'compare_price' => 9000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Kare Yaka Tafta Prenses Abiye',
                'price' => 9099.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$lacivert, $bordo, $siyah],
            ],
            [
                'category' => $prenses, 'name' => 'Güpür Korse Prenses Abiye',
                'price' => 11999.00, 'compare_price' => 14500.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $pudra],
            ],
            [
                'category' => $prenses, 'name' => 'Renkli Çiçek İşlemeli Prenses Abiye',
                'price' => 8299.00, 'compare_price' => 10200.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $yesil],
            ],
            [
                'category' => $prenses, 'name' => 'Simli Tül Katmanlı Prenses Abiye',
                'price' => 9599.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$gumus, $altin, $pudra],
            ],
            [
                'category' => $prenses, 'name' => 'Midi Boy Tül Etekli Prenses Abiye',
                'price' => 6799.00, 'compare_price' => 8300.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$pudra, $lacivert, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Kristal Kemeli Lüks Prenses Abiye',
                'price' => 15999.00, 'compare_price' => 19500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $altin, $gumus],
            ],
            [
                'category' => $prenses, 'name' => 'Kısa Kollu Dantelı Prenses Abiye',
                'price' => 7899.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$beyaz, $lacivert, $mor],
            ],
            [
                'category' => $prenses, 'name' => 'Organze Fırfırlı Prenses Abiye',
                'price' => 10499.00, 'compare_price' => 12900.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$kirmizi, $beyaz, $pudra],
            ],
            [
                'category' => $prenses, 'name' => 'Klasik Straplez Kabarık Prenses Abiye',
                'price' => 8099.00, 'compare_price' => 9800.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$siyah, $lacivert, $bordo],
            ],
            [
                'category' => $prenses, 'name' => 'V Yaka Büzgülü Şifon Prenses Abiye',
                'price' => 7199.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$pudra, $beyaz, $yesil],
            ],
            [
                'category' => $prenses, 'name' => 'Tüylü Etek Ucu Prenses Abiye',
                'price' => 12499.00, 'compare_price' => 15200.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $siyah],
            ],
            [
                'category' => $prenses, 'name' => 'Dantelli Korse Uzun Etekli Prenses Abiye',
                'price' => 9899.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $lacivert, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Puf Kollu Balon Etekli Prenses Abiye',
                'price' => 8499.00, 'compare_price' => 10400.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$pudra, $kirmizi, $lacivert],
            ],
            [
                'category' => $prenses, 'name' => 'El Yapımı Çiçek Detaylı Prenses Abiye',
                'price' => 13999.00, 'compare_price' => 17000.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $pudra],
            ],
            [
                'category' => $prenses, 'name' => 'Askısız Kristal Payetli Prenses Abiye',
                'price' => 10799.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$gumus, $altin, $beyaz],
            ],
            [
                'category' => $prenses, 'name' => 'Düğmeli Uzun Kollu Şık Prenses Abiye',
                'price' => 8999.00, 'compare_price' => 11000.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$siyah, $bordo, $lacivert],
            ],
            [
                'category' => $prenses, 'name' => 'Kalp Yaka Simli Tül Prenses Abiye',
                'price' => 7699.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$pudra, $beyaz, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Kuyruklu Şale Detaylı Prenses Abiye',
                'price' => 16999.00, 'compare_price' => 21000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $altin],
            ],
            [
                'category' => $prenses, 'name' => 'Payetli Korse Tül Etekli Prenses Abiye',
                'price' => 11299.00, 'compare_price' => 13800.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$gumus, $altin, $pudra],
            ],
            [
                'category' => $prenses, 'name' => 'Derin Sırt Dekolte Prenses Abiye',
                'price' => 9299.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$siyah, $lacivert, $bordo],
            ],
            [
                'category' => $prenses, 'name' => 'Degrade Renk Geçişli Tül Prenses Abiye',
                'price' => 10099.00, 'compare_price' => 12400.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$mor, $lacivert, $pudra],
            ],
            [
                'category' => $prenses, 'name' => 'Askılı Organze Kabarık Prenses Abiye',
                'price' => 7999.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$kirmizi, $beyaz, $lacivert],
            ],
            [
                'category' => $prenses, 'name' => 'Boncuklu Mini Prenses Abiye',
                'price' => 5999.00, 'compare_price' => 7500.00,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$pudra, $beyaz, $mor],
            ],
            [
                'category' => $prenses, 'name' => 'Geniş Kemer Detaylı Prenses Abiye',
                'price' => 9599.00, 'compare_price' => 11700.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$beyaz, $siyah, $bordo],
            ],
            [
                'category' => $prenses, 'name' => 'Balık Korse Prenses Kombinasyonu Abiye',
                'price' => 10599.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $lacivert, $kirmizi],
            ],
            [
                'category' => $prenses, 'name' => 'Çiçek Baskılı Şifon Prenses Abiye',
                'price' => 6499.00, 'compare_price' => 8000.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $yesil],
            ],

            // ── ASKILI ABİYE (35 ürün) ────────────────────────────────────────
            [
                'category' => $askili, 'name' => 'İnci İşlemeli Balık Abiye',
                'price' => 8999.00, 'compare_price' => 11000.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $kirmizi],
            ],
            [
                'category' => $askili, 'name' => 'İnce Askılı Saten Uzun Abiye',
                'price' => 4299.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$siyah, $kirmizi, $bordo],
            ],
            [
                'category' => $askili, 'name' => 'Çift Askılı Payetli Uzun Abiye',
                'price' => 5999.00, 'compare_price' => 7500.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $askili, 'name' => 'V Yaka İnce Askılı Şifon Abiye',
                'price' => 3999.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Askılı Kristal Taş İşlemeli Abiye',
                'price' => 7499.00, 'compare_price' => 9200.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $bordo, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Omuz Askılı Boncuklu Midi Abiye',
                'price' => 5299.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$kirmizi, $siyah, $bordo],
            ],
            [
                'category' => $askili, 'name' => 'İnce Askılı Derin V Yaka Saten Abiye',
                'price' => 4799.00, 'compare_price' => 6000.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $kirmizi, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Kalın Askılı Dantelı Uzun Abiye',
                'price' => 6299.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Halter Yaka Simli Abiye',
                'price' => 5499.00, 'compare_price' => 6800.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $askili, 'name' => 'Boyun Bağlamalı Arka Açık Abiye',
                'price' => 6799.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $bordo, $kirmizi],
            ],
            [
                'category' => $askili, 'name' => 'Askılı Organze Yırtmaçlı Abiye',
                'price' => 5199.00, 'compare_price' => 6500.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$lacivert, $siyah, $mor],
            ],
            [
                'category' => $askili, 'name' => 'İp Askılı Volan Detaylı Abiye',
                'price' => 4499.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$kirmizi, $pudra, $beyaz],
            ],
            [
                'category' => $askili, 'name' => 'Çapraz Askılı Sırt Dekolte Abiye',
                'price' => 6099.00, 'compare_price' => 7500.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $bordo, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Askılı Transparan Tül Detaylı Abiye',
                'price' => 7299.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $lacivert, $gumus],
            ],
            [
                'category' => $askili, 'name' => 'Mini Askılı Pul Payetli Abiye',
                'price' => 4199.00, 'compare_price' => 5300.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$gumus, $altin, $siyah],
            ],
            [
                'category' => $askili, 'name' => 'Tek Omuz Askılı Şifon Abiye',
                'price' => 5799.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $kirmizi],
            ],
            [
                'category' => $askili, 'name' => 'Uzun İnce Askılı Boncuk İşlemeli Abiye',
                'price' => 8199.00, 'compare_price' => 10000.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$lacivert, $siyah, $bordo],
            ],
            [
                'category' => $askili, 'name' => 'Derin Sırt Açık İnce Askılı Abiye',
                'price' => 6599.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $kirmizi, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Volanlı Askılı Uzun Şifon Abiye',
                'price' => 5099.00, 'compare_price' => 6400.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$pudra, $beyaz, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Kare Yaka Kalın Askılı Saten Abiye',
                'price' => 4699.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$siyah, $bordo, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Askılı Güpür Dantelı Midi Abiye',
                'price' => 5699.00, 'compare_price' => 7000.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $siyah],
            ],
            [
                'category' => $askili, 'name' => 'Boyundan Bağlamalı Payetli Abiye',
                'price' => 7099.00, 'compare_price' => null,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$altin, $gumus, $siyah],
            ],
            [
                'category' => $askili, 'name' => 'İnce Askılı Yırtmaçlı Saten Abiye',
                'price' => 4899.00, 'compare_price' => 6100.00,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$kirmizi, $siyah, $bordo],
            ],
            [
                'category' => $askili, 'name' => 'Taş Detaylı Askılı Kadife Abiye',
                'price' => 6399.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$bordo, $siyah, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Çift Omuz Dekolteli Uzun Askılı Abiye',
                'price' => 5599.00, 'compare_price' => 6900.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $lacivert, $mor],
            ],
            [
                'category' => $askili, 'name' => 'El Yapımı Nakış Detaylı Askılı Abiye',
                'price' => 9799.00, 'compare_price' => 12000.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $bordo, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Bağcıklı Sırt Dekolte Askılı Abiye',
                'price' => 6899.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$siyah, $kirmizi, $gumus],
            ],
            [
                'category' => $askili, 'name' => 'Kanat Kol Simli Askılı Abiye',
                'price' => 7599.00, 'compare_price' => 9200.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$altin, $gumus, $siyah],
            ],
            [
                'category' => $askili, 'name' => 'Askılı Tek Yırtmaçlı Dantelli Abiye',
                'price' => 5399.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$beyaz, $pudra, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Lüks Kumaş İnce Askılı Abiye',
                'price' => 8699.00, 'compare_price' => 10500.00,
                'is_featured' => true, 'is_new' => false,
                'colors' => [$siyah, $bordo, $lacivert],
            ],
            [
                'category' => $askili, 'name' => 'Çiçek Motifli Askılı Midi Abiye',
                'price' => 4999.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$beyaz, $pudra, $yesil],
            ],
            [
                'category' => $askili, 'name' => 'Korse Detaylı Askılı Uzun Abiye',
                'price' => 7899.00, 'compare_price' => 9600.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $lacivert, $kirmizi],
            ],
            [
                'category' => $askili, 'name' => 'Püskül Detaylı Bohem Askılı Abiye',
                'price' => 5799.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => false,
                'colors' => [$bordo, $lacivert, $yesil],
            ],
            [
                'category' => $askili, 'name' => 'Tüy Etek Ucu Detaylı Askılı Abiye',
                'price' => 9499.00, 'compare_price' => 11800.00,
                'is_featured' => true, 'is_new' => true,
                'colors' => [$siyah, $beyaz, $pudra],
            ],
            [
                'category' => $askili, 'name' => 'Midi Boy Büzgülü Askılı Şifon Abiye',
                'price' => 4599.00, 'compare_price' => null,
                'is_featured' => false, 'is_new' => true,
                'colors' => [$pudra, $beyaz, $kirmizi],
            ],
        ];

        foreach ($urunler as $data) {
            if (!$data['category']) continue;

            $product = Product::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id'       => $data['category']->id,
                    'name'              => $data['name'],
                    'slug'              => Str::slug($data['name']),
                    'price'             => $data['price'],
                    'compare_price'     => $data['compare_price'],
                    'stock'             => 0,
                    'track_stock'       => true,
                    'is_active'         => true,
                    'is_featured'       => $data['is_featured'],
                    'is_new'            => $data['is_new'],
                    'short_description' => 'Özel tasarım, yüksek kalite.',
                ]
            );

            foreach ($data['colors'] as $color) {
                if (!$color) continue;
                foreach ($bedenler as $size) {
                    ProductVariant::updateOrCreate(
                        [
                            'product_id' => $product->id,
                            'color_id'   => $color->id,
                            'size_id'    => $size->id,
                        ],
                        [
                            'stock'          => rand(0, 15),
                            'price_modifier' => 0,
                            'is_active'      => true,
                            'sku'            => strtoupper(Str::random(8)),
                        ]
                    );
                }
            }
        }
    }
}