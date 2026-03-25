<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;

class OrderItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    protected static ?string $title = 'Sipariş Kalemleri';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('product.main_image')
                    ->label('')
                    ->disk('public')
                    ->width(60)
                    ->height(80)
                    ->defaultImageUrl(asset('images/placeholder.png')),

                TextColumn::make('product_name')
                    ->label('Ürün')
                    ->description(fn($record) => $record->variant_info)
                    ->wrap(),

                TextColumn::make('unit_price')
                    ->label('Birim Fiyat')
                    ->money('TRY'),

                TextColumn::make('quantity')
                    ->label('Adet')
                    ->alignCenter(),

                TextColumn::make('total_price')
                    ->label('Toplam')
                    ->money('TRY')
                    ->weight('bold'),
            ])
            ->paginated(false)
            ->striped();
    }
}