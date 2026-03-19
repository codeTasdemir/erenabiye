<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource as OrdersOrderResource;
use App\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestOrders extends BaseWidget
{
    protected static ?int $sort = 3;
    protected static ?string $heading = 'Son Siparişler';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::latest()->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->searchable()
                    ->copyable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('shipping_name')
                    ->label('Müşteri'),

                Tables\Columns\TextColumn::make('shipping_city')
                    ->label('Şehir'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending'    => 'Beklemede',
                        'confirmed'  => 'Onaylandı',
                        'processing' => 'Hazırlanıyor',
                        'shipped'    => 'Kargoya Verildi',
                        'delivered'  => 'Teslim Edildi',
                        'cancelled'  => 'İptal Edildi',
                        'refunded'   => 'İade Edildi',
                        default      => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'pending'                 => 'warning',
                        'confirmed', 'processing' => 'info',
                        'shipped'                 => 'primary',
                        'delivered'               => 'success',
                        'cancelled', 'refunded'   => 'danger',
                        default                   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Ödeme')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending'  => 'Bekliyor',
                        'paid'     => 'Ödendi',
                        'failed'   => 'Başarısız',
                        'refunded' => 'İade',
                        default    => $state,
                    })
                    ->color(fn($state) => match ($state) {
                        'pending'            => 'warning',
                        'paid'               => 'success',
                        'failed', 'refunded' => 'danger',
                        default              => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Toplam')
                    ->money('TRY'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->recordUrl(fn(Order $record) => OrdersOrderResource::getUrl('edit', ['record' => $record]));

    }
}
