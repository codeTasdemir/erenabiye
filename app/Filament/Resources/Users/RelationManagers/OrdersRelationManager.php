<?php

namespace App\Filament\Resources\Users\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Schemas\Schema;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Siparişler';
    protected static ?string $modelLabel        = 'Sipariş';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Tarih')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Durum')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pending'    => 'warning',
                        'confirmed'  => 'info',
                        'processing' => 'info',
                        'shipped'    => 'primary',
                        'delivered'  => 'success',
                        'cancelled'  => 'danger',
                        'refunded'   => 'gray',
                        default      => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'    => 'Beklemede',
                        'confirmed'  => 'Onaylandı',
                        'processing' => 'Hazırlanıyor',
                        'shipped'    => 'Kargoda',
                        'delivered'  => 'Teslim Edildi',
                        'cancelled'  => 'İptal',
                        'refunded'   => 'İade',
                        default      => $state,
                    }),

                TextColumn::make('payment_status')
                    ->label('Ödeme')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid'     => 'success',
                        'pending'  => 'warning',
                        'failed'   => 'danger',
                        'refunded' => 'gray',
                        default    => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pending'  => 'Bekliyor',
                        'paid'     => 'Ödendi',
                        'failed'   => 'Başarısız',
                        'refunded' => 'İade',
                        default    => $state,
                    }),

                TextColumn::make('cargo_company')
                    ->label('Kargo')
                    ->placeholder('—'),

                TextColumn::make('cargo_tracking_number')
                    ->label('Takip No')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('shipped_at')
                    ->label('Kargoya Verildi')
                    ->dateTime('d.m.Y')
                    ->placeholder('—'),

                TextColumn::make('delivered_at')
                    ->label('Teslim Edildi')
                    ->dateTime('d.m.Y')
                    ->placeholder('—'),

                TextColumn::make('total')
                    ->label('Toplam')
                    ->money('TRY')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                \Filament\Actions\Action::make('view_order')
                    ->label('Detay')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('filament.admin.resources.orders.edit', $record)),
            ]);
    }
}
