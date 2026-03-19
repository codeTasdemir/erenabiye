<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\CreateOrder;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Schemas\OrderForm;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\Orders\RelationManagers;



class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'order_number';
    protected static ?string $navigationLabel = 'Siparişler';
    protected static ?string $modelLabel = 'Sipariş';
    protected static ?string $pluralModelLabel = 'Siparişler';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return 'Siparişler';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Section::make('Sipariş Bilgileri')
                ->schema([
                    TextInput::make('order_number')
                        ->label('Sipariş No')
                        ->disabled(),

                    Select::make('status')
                        ->label('Sipariş Durumu')
                        ->options([
                            'pending'    => 'Beklemede',
                            'confirmed'  => 'Onaylandı',
                            'processing' => 'Hazırlanıyor',
                            'shipped'    => 'Kargoya Verildi',
                            'delivered'  => 'Teslim Edildi',
                            'cancelled'  => 'İptal Edildi',
                            'refunded'   => 'İade Edildi',
                        ])
                        ->required(),

                    Select::make('payment_status')
                        ->label('Ödeme Durumu')
                        ->options([
                            'pending'  => 'Ödeme Bekleniyor',
                            'paid'     => 'Ödendi',
                            'failed'   => 'Başarısız',
                            'refunded' => 'İade Edildi',
                        ])
                        ->required(),

                    Select::make('currency')
                        ->label('Para Birimi')
                        ->options([
                            'TRY' => 'Türk Lirası (₺)',
                            'USD' => 'Amerikan Doları ($)',
                            'EUR' => 'Euro (€)',
                        ])
                        ->default('TRY'),
                ])->columns(2),

            Section::make('Teslimat Bilgileri')
                ->schema([
                    TextInput::make('shipping_name')
                        ->label('Ad Soyad')
                        ->required(),

                    TextInput::make('shipping_phone')
                        ->label('Telefon')
                        ->required(),

                    TextInput::make('shipping_city')
                        ->label('Şehir')
                        ->required(),

                    TextInput::make('shipping_district')
                        ->label('İlçe')
                        ->required(),

                    Textarea::make('shipping_address')
                        ->label('Açık Adres')
                        ->rows(2)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Kargo Bilgileri')
                ->schema([
                    TextInput::make('cargo_company')
                        ->label('Kargo Firması'),

                    TextInput::make('cargo_tracking_number')
                        ->label('Kargo Takip No'),

                    DateTimePicker::make('shipped_at')
                        ->label('Kargoya Verilme Tarihi'),

                    DateTimePicker::make('delivered_at')
                        ->label('Teslim Tarihi'),
                ])->columns(2),

            Section::make('Finansal Bilgiler')
                ->schema([
                    TextInput::make('subtotal')
                        ->label('Ara Toplam')
                        ->prefix('₺')
                        ->disabled(),

                    TextInput::make('discount_amount')
                        ->label('İndirim')
                        ->prefix('₺')
                        ->disabled(),

                    TextInput::make('shipping_amount')
                        ->label('Kargo Ücreti')
                        ->prefix('₺')
                        ->disabled(),

                    TextInput::make('total')
                        ->label('Toplam')
                        ->prefix('₺')
                        ->disabled(),
                ])->columns(2),

            Section::make('Notlar')
                ->schema([
                    Textarea::make('notes')
                        ->label('Sipariş Notu')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('shipping_name')
                    ->label('Müşteri')
                    ->searchable(),

                TextColumn::make('shipping_phone')
                    ->label('Telefon')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'    => 'Beklemede',
                        'confirmed'  => 'Onaylandı',
                        'processing' => 'Hazırlanıyor',
                        'shipped'    => 'Kargoya Verildi',
                        'delivered'  => 'Teslim Edildi',
                        'cancelled'  => 'İptal Edildi',
                        'refunded'   => 'İade Edildi',
                        default      => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending'               => 'warning',
                        'confirmed', 'processing' => 'info',
                        'shipped'               => 'primary',
                        'delivered'             => 'success',
                        'cancelled', 'refunded' => 'danger',
                        default                 => 'gray',
                    }),

                TextColumn::make('items_count')
                    ->label('Ürünler')
                    ->counts('items')
                    ->badge()
                    ->color('gray')
                    ->suffix(' kalem'),

                TextColumn::make('payment_status')
                    ->label('Ödeme')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'  => 'Bekliyor',
                        'paid'     => 'Ödendi',
                        'failed'   => 'Başarısız',
                        'refunded' => 'İade',
                        default    => $state,
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'pending'            => 'warning',
                        'paid'               => 'success',
                        'failed', 'refunded' => 'danger',
                        default              => 'gray',
                    }),

                TextColumn::make('total')
                    ->label('Toplam')
                    ->money('TRY')
                    ->sortable(),

                TextColumn::make('currency')
                    ->label('Para Birimi')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Sipariş Durumu')
                    ->options([
                        'pending'    => 'Beklemede',
                        'confirmed'  => 'Onaylandı',
                        'processing' => 'Hazırlanıyor',
                        'shipped'    => 'Kargoya Verildi',
                        'delivered'  => 'Teslim Edildi',
                        'cancelled'  => 'İptal Edildi',
                        'refunded'   => 'İade Edildi',
                    ]),

                SelectFilter::make('payment_status')
                    ->label('Ödeme Durumu')
                    ->options([
                        'pending'  => 'Bekliyor',
                        'paid'     => 'Ödendi',
                        'failed'   => 'Başarısız',
                        'refunded' => 'İade',
                    ]),

                Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')
                            ->label('Başlangıç Tarihi'),
                        DatePicker::make('until')
                            ->label('Bitiş Tarihi'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'],  fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Düzenle'),
                ViewAction::make()
                    ->label('Detay'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrderItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
            'view'   => Pages\ViewOrder::route('/{record}'),
        ];
    }
}
