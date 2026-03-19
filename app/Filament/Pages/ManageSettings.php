<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use BackedEnum;

class ManageSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $navigationLabel = 'Site Ayarları';
    protected static ?string $title = 'Site Ayarları';
    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-settings';

    public static function getNavigationGroup(): string
    {
        return 'Sistem';
    }

    // ── GENEL ──
    public ?string $site_name        = null;
    public ?string $site_description = null;
    public ?string $currency         = null;
    public ?string $currency_symbol  = null;
    public array   $site_logo        = [];

    // ── İLETİŞİM ──
    public ?string $contact_phone   = null;
    public ?string $contact_phone2  = null;
    public ?string $contact_email   = null;
    public ?string $contact_address = null;

    // ── SOSYAL MEDYA ──
    public ?string $social_instagram = null;
    public ?string $social_facebook  = null;
    public ?string $social_tiktok    = null;
    public ?string $social_whatsapp  = null;

    // ── KARGO ──
    public ?string $free_shipping_threshold = null;
    public ?string $shipping_cost           = null;

    // ── ÖDEME ──
    public ?string $paytr_merchant_id   = null;
    public ?string $paytr_merchant_key  = null;
    public ?string $paytr_merchant_salt = null;
    public ?string $paytr_test_mode     = null;

    // ── SEO ──
    public ?string $meta_title       = null;
    public ?string $meta_description = null;
    public ?string $meta_keywords    = null;
    public ?string $google_analytics = null;

    // ── E-POSTA ──
    public ?string $mail_from_name     = null;
    public ?string $mail_from_address  = null;
    public ?string $order_notify_email = null;

    // ── PAZARYERLERİ ──
    public ?string $marketplace_trendyol_url     = null;
    public array   $marketplace_trendyol_logo    = [];
    public ?string $marketplace_hepsiburada_url  = null;
    public array   $marketplace_hepsiburada_logo = [];
    public ?string $marketplace_n11_url          = null;
    public array   $marketplace_n11_logo         = [];
    public function mount(): void
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        // site_logo
        if (!empty($settings['site_logo'])) {
            $settings['site_logo'] = [$settings['site_logo']];
        } else {
            $settings['site_logo'] = [];
        }

        // pazaryeri logoları
        foreach (['marketplace_trendyol_logo', 'marketplace_hepsiburada_logo', 'marketplace_n11_logo'] as $key) {
            if (!empty($settings[$key])) {
                $settings[$key] = [$settings[$key]];
            } else {
                $settings[$key] = [];
            }
        }

        $this->fill($settings);
    }
    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make('Ayarlar')
                ->tabs([

                    // ── GENEL ──
                    Tab::make('Genel')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            TextInput::make('site_name')
                                ->label('Site Adı')
                                ->required(),

                            TextInput::make('currency')
                                ->label('Para Birimi')
                                ->default('TRY'),

                            TextInput::make('currency_symbol')
                                ->label('Para Birimi Sembolü')
                                ->default('₺'),

                            Textarea::make('site_description')
                                ->label('Site Açıklaması')
                                ->rows(2)
                                ->columnSpanFull(),

                            FileUpload::make('site_logo')
                                ->label('Site Logosu')
                                ->image()
                                ->disk('public')
                                ->directory('logos')
                                ->imagePreviewHeight('80')
                                ->maxSize(2048)
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'])
                                ->columnSpanFull(),
                        ])->columns(2),

                    // ── İLETİŞİM ──
                    Tab::make('İletişim')
                        ->icon('heroicon-o-phone')
                        ->schema([
                            TextInput::make('contact_phone')
                                ->label('Telefon 1')
                                ->tel(),

                            TextInput::make('contact_phone2')
                                ->label('Telefon 2')
                                ->tel(),

                            TextInput::make('contact_email')
                                ->label('E-posta')
                                ->email(),

                            Textarea::make('contact_address')
                                ->label('Adres')
                                ->rows(2)
                                ->columnSpanFull(),
                        ])->columns(2),

                    // ── SOSYAL MEDYA ──
                    Tab::make('Sosyal Medya')
                        ->icon('heroicon-o-heart')
                        ->schema([
                            TextInput::make('social_instagram')
                                ->label('Instagram URL')
                                ->url()
                                ->prefix('instagram.com/'),

                            TextInput::make('social_facebook')
                                ->label('Facebook URL')
                                ->url(),

                            TextInput::make('social_tiktok')
                                ->label('TikTok URL')
                                ->url(),

                            TextInput::make('social_whatsapp')
                                ->label('WhatsApp Numarası')
                                ->tel()
                                ->prefix('+90'),
                        ])->columns(2),

                    // ── KARGO ──
                    /* Tab::make('Kargo')
                        ->icon('heroicon-o-truck')
                        ->schema([
                            TextInput::make('free_shipping_threshold')
                                ->label('Ücretsiz Kargo Limiti (₺)')
                                ->numeric()
                                ->prefix('₺'),

                            TextInput::make('shipping_cost')
                                ->label('Kargo Ücreti (₺)')
                                ->numeric()
                                ->prefix('₺'),
                        ])->columns(2), */

                    // ── ÖDEME ──
                    /* Tab::make('PayTR')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            TextInput::make('paytr_merchant_id')
                                ->label('Merchant ID')
                                ->password()
                                ->revealable(),

                            TextInput::make('paytr_merchant_key')
                                ->label('Merchant Key')
                                ->password()
                                ->revealable(),

                            TextInput::make('paytr_merchant_salt')
                                ->label('Merchant Salt')
                                ->password()
                                ->revealable(),

                            Toggle::make('paytr_test_mode')
                                ->label('Test Modu')
                                ->helperText('Canlıya geçmeden önce kapatmayı unutmayın!'),
                        ])->columns(2), */

                    // ── SEO ──
                    Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('Site Meta Başlığı')
                                ->columnSpanFull(),

                            TextInput::make('meta_keywords')
                                ->label('Meta Anahtar Kelimeler')
                                ->columnSpanFull(),

                            Textarea::make('meta_description')
                                ->label('Meta Açıklama')
                                ->rows(2)
                                ->columnSpanFull(),

                            TextInput::make('google_analytics')
                                ->label('Google Analytics ID')
                                ->placeholder('G-XXXXXXXXXX')
                                ->columnSpanFull(),
                        ]),

                    // ── E-POSTA ──
                    /* Tab::make('E-posta')
                        ->icon('heroicon-o-envelope')
                        ->schema([
                            TextInput::make('mail_from_name')
                                ->label('Gönderen Adı'),

                            TextInput::make('mail_from_address')
                                ->label('Gönderen E-posta')
                                ->email(),

                            TextInput::make('order_notify_email')
                                ->label('Sipariş Bildirim E-postası')
                                ->email(),
                        ])->columns(2),
 */
                    // ── PAZARYERLERİ ──
                    Tab::make('Pazaryerleri')
                        ->icon('heroicon-o-shopping-bag')
                        ->schema([
                            FileUpload::make('marketplace_trendyol_logo')
                                ->label('Trendyol Logo')
                                ->image()
                                ->disk('public')
                                ->directory('marketplaces')
                                ->imagePreviewHeight('60')
                                ->maxSize(1024)
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp']),

                            TextInput::make('marketplace_trendyol_url')
                                ->label('Trendyol Mağaza Linki')
                                ->url()
                                ->placeholder('https://trendyol.com/...'),

                            FileUpload::make('marketplace_hepsiburada_logo')
                                ->label('Hepsiburada Logo')
                                ->image()
                                ->disk('public')
                                ->directory('marketplaces')
                                ->imagePreviewHeight('60')
                                ->maxSize(1024)
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp']),

                            TextInput::make('marketplace_hepsiburada_url')
                                ->label('Hepsiburada Mağaza Linki')
                                ->url()
                                ->placeholder('https://hepsiburada.com/...'),

                            FileUpload::make('marketplace_n11_logo')
                                ->label('N11 Logo')
                                ->image()
                                ->disk('public')
                                ->directory('marketplaces')
                                ->imagePreviewHeight('60')
                                ->maxSize(1024)
                                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp']),

                            TextInput::make('marketplace_n11_url')
                                ->label('N11 Mağaza Linki')
                                ->url()
                                ->placeholder('https://n11.com/...'),
                        ])->columns(2),

                ])->columnSpanFull(),
        ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = $value[0] ?? null;
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->title('Ayarlar kaydedildi!')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Ayarları Kaydet')
                ->action('save')
                ->icon('heroicon-o-check')
                ->color('primary'),
        ];
    }
}
