<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages\CreateCoupon;
use App\Filament\Resources\Coupons\Pages\EditCoupon;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Coupons\Schemas\CouponForm;
use App\Filament\Resources\Coupons\Tables\CouponsTable;
use App\Models\Coupon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\Action;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'code';
    protected static ?string $navigationLabel = 'Kuponlar';
    protected static ?string $modelLabel = 'Kupon';
    protected static ?string $pluralModelLabel = 'Kuponlar';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return 'Pazarlama';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Kupon Bilgileri')
                ->schema([
                    TextInput::make('code')
                        ->label('Kupon Kodu')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(50)
                        ->suffixAction(
                            Action::make('generate')
                                ->label('Otomatik Oluştur')
                                ->icon('heroicon-o-arrow-path')
                                ->action(
                                    fn(Set $set) =>
                                    $set('code', strtoupper(\Illuminate\Support\Str::random(8)))
                                )
                        ),

                    Select::make('type')
                        ->label('İndirim Türü')
                        ->options([
                            'fixed'      => 'Sabit Tutar (₺)',
                            'percentage' => 'Yüzde (%)',
                        ])
                        ->required()
                        ->live(),

                    TextInput::make('value')
                        ->label(
                            fn(Get $get) =>
                            $get('type') === 'percentage' ? 'İndirim Oranı (%)' : 'İndirim Tutarı (₺)'
                        )
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    TextInput::make('minimum_order')
                        ->label('Minimum Sipariş Tutarı (₺)')
                        ->numeric()
                        ->default(0)
                        ->prefix('₺'),

                    TextInput::make('usage_limit')
                        ->label('Kullanım Limiti')
                        ->numeric()
                        ->nullable()
                        ->helperText('Boş bırakırsanız sınırsız kullanılabilir'),

                    DateTimePicker::make('expires_at')
                        ->label('Son Geçerlilik Tarihi')
                        ->nullable(),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kupon Kodu')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                TextColumn::make('type')
                    ->label('Tür')
                    ->badge()
                    ->formatStateUsing(fn($state) => $state === 'percentage' ? 'Yüzde' : 'Sabit')
                    ->color(fn($state) => $state === 'percentage' ? 'primary' : 'info'),

                TextColumn::make('value')
                    ->label('İndirim')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->type === 'percentage' ? "%{$state}" : "₺{$state}"
                    ),

                TextColumn::make('minimum_order')
                    ->label('Min. Sipariş')
                    ->money('TRY'),

                TextColumn::make('used_count')
                    ->label('Kullanım')
                    ->formatStateUsing(
                        fn($state, $record) =>
                        $record->usage_limit
                            ? "{$state} / {$record->usage_limit}"
                            : "{$state} / ∞"
                    ),

                TextColumn::make('expires_at')
                    ->label('Son Tarih')
                    ->dateTime('d.m.Y')
                    ->default('Süresiz')
                    ->color(
                        fn($record) =>
                        $record->expires_at && $record->expires_at->isPast() ? 'danger' : null
                    ),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Durum'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoupons::route('/'),
            'create' => CreateCoupon::route('/create'),
            'edit' => EditCoupon::route('/{record}/edit'),
        ];
    }
}
