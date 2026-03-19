<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Genel
            ['key' => 'site_name',        'value' => 'Eren Abiye',                         'group' => 'general'],
            ['key' => 'site_description', 'value' => 'Toptan ve Perakende Abiye Satışı',   'group' => 'general'],
            ['key' => 'site_logo',        'value' => null,                                  'group' => 'general'],
            ['key' => 'site_favicon',     'value' => null,                                  'group' => 'general'],
            ['key' => 'currency',         'value' => 'TRY',                                 'group' => 'general'],
            ['key' => 'currency_symbol',  'value' => '₺',                                  'group' => 'general'],

            // İletişim
            ['key' => 'contact_phone',    'value' => '+90 534 747 11 62',                  'group' => 'contact'],
            ['key' => 'contact_phone2',   'value' => '+90 505 507 22 56',                  'group' => 'contact'],
            ['key' => 'contact_email',    'value' => 'info@erenabiye.com.tr',              'group' => 'contact'],
            ['key' => 'contact_address',  'value' => 'Akdeniz Mh, Mimar Kemalettin Cd. No: 48 Kat 1 D:2 Çankaya, 35210 Konak / İzmir', 'group' => 'contact'],
            ['key' => 'contact_map',      'value' => null,                                  'group' => 'contact'],

            // Sosyal Medya
            ['key' => 'social_instagram', 'value' => 'https://instagram.com/erenabiye',    'group' => 'social'],
            ['key' => 'social_facebook',  'value' => null,                                  'group' => 'social'],
            ['key' => 'social_tiktok',    'value' => null,                                  'group' => 'social'],
            ['key' => 'social_youtube',   'value' => null,                                  'group' => 'social'],
            ['key' => 'social_whatsapp',  'value' => '+905055072256',                       'group' => 'social'],

            // Kargo
            ['key' => 'free_shipping_threshold', 'value' => '500',                          'group' => 'shipping'],
            ['key' => 'shipping_cost',            'value' => '49.90',                        'group' => 'shipping'],
            ['key' => 'same_day_shipping_limit',  'value' => '14:00',                        'group' => 'shipping'],

            // Ödeme (PayTR)
            ['key' => 'paytr_merchant_id',     'value' => null, 'group' => 'payment'],
            ['key' => 'paytr_merchant_key',    'value' => null, 'group' => 'payment'],
            ['key' => 'paytr_merchant_salt',   'value' => null, 'group' => 'payment'],
            ['key' => 'paytr_test_mode',       'value' => '1',  'group' => 'payment'],
            ['key' => 'paytr_installment',     'value' => '1',  'group' => 'payment'],

            // SEO
            ['key' => 'meta_title',       'value' => 'Eren Abiye | Toptan ve Perakende Abiye', 'group' => 'seo'],
            ['key' => 'meta_description', 'value' => 'Kaliteli ve şık abiye modelleri Eren Abiye\'de. Toptan ve perakende satış.', 'group' => 'seo'],
            ['key' => 'meta_keywords',    'value' => 'abiye, toptan abiye, balık abiye, prenses abiye, eren abiye', 'group' => 'seo'],
            ['key' => 'google_analytics', 'value' => null, 'group' => 'seo'],

            // E-posta
            ['key' => 'mail_from_name',    'value' => 'Eren Abiye',             'group' => 'email'],
            ['key' => 'mail_from_address', 'value' => 'info@erenabiye.com.tr', 'group' => 'email'],
            ['key' => 'order_notify_email', 'value' => 'info@erenabiye.com.tr', 'group' => 'email'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }
}
