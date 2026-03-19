<?php

namespace App\Filament\Resources\Products;

use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Product;
use App\Models\Category;
use App\Models\Color;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ColorPicker;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel     = 'Ürünler';
    protected static ?string $modelLabel          = 'Ürün';
    protected static ?string $pluralModelLabel    = 'Ürünler';
    protected static ?int    $navigationSort      = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([

            Tabs::make('Ürün Detayları')
                ->tabs([

                    Tab::make('Temel Bilgiler')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            TextInput::make('name')
                                ->label('Ürün Adı')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(
                                    fn(Set $set, ?string $state) =>
                                    $set('slug', Str::slug($state ?? ''))
                                )
                                ->columnSpanFull(),

                            TextInput::make('slug')
                                ->label('Slug (URL)')
                                ->required()
                                ->unique(ignoreRecord: true)
                                ->maxLength(255),

                            Select::make('category_id')
                                ->label('Kategori')
                                ->options(Category::where('is_active', true)->pluck('name', 'id'))
                                ->searchable()
                                ->required(),

                            TextInput::make('sku')
                                ->label('Stok Kodu (SKU)')
                                ->unique(ignoreRecord: true)
                                ->maxLength(100),

                            Textarea::make('short_description')
                                ->label('Kısa Açıklama')
                                ->rows(2)
                                ->columnSpanFull(),

                            RichEditor::make('description')
                                ->label('Detaylı Açıklama')
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'underline',
                                    'bulletList',
                                    'orderedList',
                                    'h2',
                                    'h3',
                                    'link',
                                    'undo',
                                    'redo',
                                ])
                                ->columnSpanFull(),
                        ])->columns(2),

                    Tab::make('Fiyat & Stok')
                        ->icon('heroicon-o-currency-dollar')
                        ->schema([
                            TextInput::make('price')
                                ->label('Satış Fiyatı (₺)')
                                ->numeric()
                                ->required()
                                ->prefix('₺'),

                            TextInput::make('compare_price')
                                ->label('Karşılaştırma Fiyatı (₺)')
                                ->numeric()
                                ->prefix('₺')
                                ->helperText('Üstü çizili eski fiyat için'),

                            TextInput::make('cost_price')
                                ->label('Maliyet Fiyatı (₺)')
                                ->numeric()
                                ->prefix('₺')
                                ->helperText('Sadece admin görebilir'),

                            TextInput::make('stock')
                                ->label('Stok Miktarı')
                                ->numeric()
                                ->default(0),

                            TextInput::make('low_stock_alert')
                                ->label('Düşük Stok Uyarı Limiti')
                                ->numeric()
                                ->default(5),

                            Toggle::make('track_stock')
                                ->label('Stok Takibi Yap')
                                ->default(true),
                        ])->columns(2),

                    Tab::make('Görseller')
                        ->icon('heroicon-o-photo')
                        ->schema([

                            FileUpload::make('main_image')
                                ->label('Ana Görsel')
                                ->image()
                                ->disk('public')
                                ->directory('products')
                                ->imageEditor()
                                ->columnSpanFull(),

                            Section::make('Genel Görseller')
                                ->description('Tüm renklerde görünecek görseller. Renk seçilmediğinde bu görseller gösterilir.')
                                ->icon('heroicon-o-photo')
                                ->collapsible()
                                ->schema([
                                    Repeater::make('generalImages')
                                        ->label('')
                                        ->relationship('generalImages')
                                        ->schema([
                                            FileUpload::make('image')
                                                ->label('Görsel')
                                                ->image()
                                                ->disk('public')
                                                ->directory('products/general')
                                                ->imageEditor()
                                                ->required(),

                                            TextInput::make('alt_text')
                                                ->label('Alt Metin (SEO)')
                                                ->maxLength(255),

                                            TextInput::make('sort_order')
                                                ->label('Sıra')
                                                ->numeric()
                                                ->default(0),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel('+ Genel Görsel Ekle')
                                        ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                            $data['color_id'] = null;
                                            return $data;
                                        }),
                                ])
                                ->columnSpanFull(),

                            Section::make('Renge Özel Görseller')
                                ->description('Her renk için ayrı görsel seti ekleyebilirsiniz. Kullanıcı renk seçtiğinde bu görseller gösterilir.')
                                ->icon('heroicon-o-swatch')
                                ->collapsible()
                                ->schema([
                                    Repeater::make('colorImages')
                                        ->label('')
                                        ->relationship('colorImages')
                                        ->schema([
                                            Select::make('color_id')
                                                ->label('Renk')
                                                ->options(
                                                    Color::orderBy('name')
                                                        ->get()
                                                        ->mapWithKeys(fn($c) => [$c->id => $c->name])
                                                )
                                                ->searchable()
                                                ->required()
                                                ->columnSpanFull(),

                                            FileUpload::make('image')
                                                ->label('Görsel')
                                                ->image()
                                                ->disk('public')
                                                ->directory('products/colors')
                                                ->imageEditor()
                                                ->required(),

                                            TextInput::make('alt_text')
                                                ->label('Alt Metin (SEO)')
                                                ->maxLength(255),

                                            TextInput::make('sort_order')
                                                ->label('Sıra')
                                                ->numeric()
                                                ->default(0),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel('+ Renge Özel Görsel Ekle')
                                        ->collapsible()
                                        ->itemLabel(function (array $state): ?string {
                                            $colorId = $state['color_id'] ?? null;
                                            if (! $colorId) return 'Renk seçilmedi';
                                            $colorName = Color::find($colorId)?->name ?? "Renk #$colorId";
                                            return "🎨 $colorName";
                                        }),
                                ])
                                ->columnSpanFull(),
                        ]),




                    Tab::make('Catwalk Videoları')
                        ->icon('heroicon-o-video-camera')
                        ->schema([
                            Repeater::make('colorVideos')
                                ->label('Renge Özel Catwalk Videoları')
                                ->relationship('colorVideos')
                                ->schema([
                                    Select::make('color_id')
                                        ->label('Renk')
                                        ->options(
                                            \App\Models\Color::orderBy('name')
                                                ->get()
                                                ->mapWithKeys(fn($c) => [$c->id => $c->name])
                                        )
                                        ->searchable()
                                        ->required(),

                                    TextInput::make('video_url')
                                        ->label('YouTube Shorts URL')
                                        ->placeholder('https://www.youtube.com/shorts/VIDEO_ID')
                                        ->helperText('YouTube Shorts, youtu.be veya watch?v= formatları desteklenir.')
                                        ->url()
                                        ->required()
                                        ->maxLength(255),
                                ])
                                ->columns(2)
                                ->addActionLabel('+ Video Ekle')
                                ->itemLabel(function (array $state): ?string {
                                    $colorId = $state['color_id'] ?? null;
                                    if (!$colorId) return 'Renk seçilmedi';
                                    $colorName = \App\Models\Color::find($colorId)?->name ?? "Renk #$colorId";
                                    return " $colorName";
                                })
                                ->columnSpanFull(),
                        ]),
                    Tab::make('Varyantlar')
                        ->icon('heroicon-o-swatch')
                        ->schema([
                            Placeholder::make('variant_info')
                                ->label('')
                                ->content('Her renk-beden kombinasyonu için ayrı stok ve fiyat girebilirsiniz. Görseller için "Görseller" sekmesini kullanın.')
                                ->columnSpanFull(),

                            Repeater::make('variants')
                                ->label('Renk & Beden Varyantları')
                                ->relationship('variants')
                                ->schema([
                                    Select::make('color_id')
                                        ->label('Renk')
                                        ->relationship('color', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Renk Adı')
                                                ->required(),
                                            ColorPicker::make('hex_code')
                                                ->label('Renk Kodu'),
                                        ]),

                                    Select::make('size_id')
                                        ->label('Beden')
                                        ->relationship('size', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->createOptionForm([
                                            TextInput::make('name')
                                                ->label('Beden Adı')
                                                ->required(),
                                            TextInput::make('label')
                                                ->label('Etiket'),
                                        ]),

                                    TextInput::make('stock')
                                        ->label('Stok')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->suffix('adet'),

                                    TextInput::make('price_modifier')
                                        ->label('Fiyat Farkı')
                                        ->numeric()
                                        ->default(0)
                                        ->prefix('₺')
                                        ->helperText('+ veya - değer girilebilir'),

                                    TextInput::make('sku')
                                        ->label('Varyant SKU')
                                        ->maxLength(100)
                                        ->placeholder('Otomatik oluşturulur'),

                                    Toggle::make('is_active')
                                        ->label('Aktif')
                                        ->default(true)
                                        ->columnSpan(2),
                                ])
                                ->columns(3)
                                ->columnSpanFull()
                                ->addActionLabel('+ Varyant Ekle')
                                ->cloneable()
                                ->collapsible()
                                ->collapsed()
                                ->itemLabel(function (array $state): ?string {
                                    $colorId = $state['color_id'] ?? null;
                                    $sizeId  = $state['size_id'] ?? null;
                                    $stock   = $state['stock'] ?? 0;

                                    $color = $colorId
                                        ? \App\Models\Color::find($colorId)?->name ?? "Renk #$colorId"
                                        : 'Renk seçilmedi';

                                    $size = $sizeId
                                        ? \App\Models\Size::find($sizeId)?->name ?? "Beden #$sizeId"
                                        : 'Beden seçilmedi';

                                    return "$color — $size | Stok: $stock";
                                }),
                        ]),

                    Tab::make('SEO')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            TextInput::make('meta_title')
                                ->label('Meta Başlık')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            TextInput::make('meta_keywords')
                                ->label('Anahtar Kelimeler')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Textarea::make('meta_description')
                                ->label('Meta Açıklama')
                                ->rows(3)
                                ->maxLength(500)
                                ->columnSpanFull(),
                        ]),

                ])->columnSpanFull(),

            Section::make('Yayın Ayarları')
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                    Toggle::make('is_featured')
                        ->label('Öne Çıkan')
                        ->default(false),

                    Toggle::make('is_new')
                        ->label('Yeni Ürün')
                        ->default(true),

                    Toggle::make('is_weekly_product')
                        ->label('Haftanın Ürünü')
                        ->default(false),

                    TextInput::make('sort_order')
                        ->label('Sıralama')
                        ->numeric()
                        ->default(0),
                ])
                ->columnSpan(1),

        ])->columns([
            'default' => 1,
            'lg'      => 3,
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('main_image')
                    ->label('Görsel')
                    ->square(),

                TextColumn::make('name')
                    ->label('Ürün Adı')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->sortable()
                    ->badge(),

                TextColumn::make('price')
                    ->label('Fiyat')
                    ->money('TRY')
                    ->sortable(),

                TextColumn::make('variants_sum_stock')
                    ->label('Stok')
                    ->getStateUsing(function ($record): string {
                        $hasVariants = ($record->variants_count ?? 0) > 0;
                        return $hasVariants
                            ? (string) ($record->variants_sum_stock ?? 0)
                            : (string) $record->stock;
                    })
                    ->color(function ($record): string {
                        $hasVariants = ($record->variants_count ?? 0) > 0;
                        $stock = $hasVariants
                            ? ($record->variants_sum_stock ?? 0)
                            : $record->stock;
                        return $stock <= $record->low_stock_alert ? 'danger' : 'success';
                    })
                    ->sortable(),

                IconColumn::make('is_featured')
                    ->label('Öne Çıkan')
                    ->boolean(),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),

                TextColumn::make('created_at')
                    ->label('Eklenme')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->options(function () {
                        return Category::where('is_active', true)
                            ->get()
                            ->mapWithKeys(function ($category) {
                                $prefix = str_repeat('— ', $category->depth ?? 0);
                                return [$category->id => $prefix . $category->name];
                            });
                    })
                    ->searchable(),

                TernaryFilter::make('is_active')
                    ->label('Durum'),

                TernaryFilter::make('is_featured')
                    ->label('Öne Çıkan'),

                Filter::make('low_stock')
                    ->label('Düşük Stok')
                    ->query(fn($query) => $query->whereColumn('stock', '<=', 'low_stock_alert')),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount(['variants'])
            ->withSum('variants', 'stock');
    }
}
