<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Products\ProductResource as ProductsProductResource;
use App\Models\Product;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlert extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = '⚠️ Düşük Stok Uyarısı';
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::lowStock()->orderByRaw('stock ASC'))

            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Görsel')
                    ->square(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Mevcut Stok')
                    ->color(fn($record) => $record->stock <= 0 ? 'danger' : 'warning')
                    ->weight('bold')
                    ->formatStateUsing(fn($state) => $state <= 0 ? '🔴 Tükendi' : "🟡 {$state} adet"),

                Tables\Columns\TextColumn::make('low_stock_alert')
                    ->label('Uyarı Limiti')
                    ->suffix(' adet'),

                Tables\Columns\TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY'),
            ])
            ->recordUrl(fn(Product $record) => ProductsProductResource::getUrl('edit', ['record' => $record]))

            ->emptyStateHeading('Düşük stok yok.')
            ->emptyStateDescription('Tüm ürünlerin stoğu yeterli seviyede.');
    }
}
