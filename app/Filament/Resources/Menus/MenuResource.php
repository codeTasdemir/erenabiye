<?php

namespace App\Filament\Resources\Menus;

use App\Filament\Resources\Menus\Pages\CreateMenu;
use App\Filament\Resources\Menus\Pages\EditMenu;
use App\Filament\Resources\Menus\Pages\ListMenus;
use App\Filament\Resources\Menus\Schemas\MenuForm;
use App\Filament\Resources\Menus\Tables\MenusTable;
use App\Models\Menu;
use BackedEnum;
use App\Models\Category;
use App\Models\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Menüler';
    protected static ?string $modelLabel      = 'Menü';
    protected static ?int    $navigationSort  = 1;

    public static function getNavigationGroup(): string
    {
        return 'İçerik';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([

            TextInput::make('name')
                ->label('Menü Adı')
                ->required(),

            Select::make('location')
                ->label('Konum')
                ->required()
                ->options([
                    'header'   => 'Header — Ana Menü',
                    'footer_1' => 'Footer — Kurumsal',
                    'footer_2' => 'Footer — Yardım',
                    'footer_3' => 'Footer — Kategoriler',
                ]),

            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),

            Repeater::make('items')
                ->label('Menü Öğeleri')
                ->relationship('items')
                ->schema([
                    TextInput::make('label')
                        ->label('Başlık')
                        ->required()
                        ->columnSpan(2),

                    Select::make('type')
                        ->label('Tür')
                        ->options([
                            'custom'   => 'Manuel URL',
                            'category' => 'Kategori',
                            'page'     => 'Sayfa',
                            'blog'     => 'Blog',
                        ])
                        ->default('custom')
                        ->live(),

                    TextInput::make('url')
                        ->label('URL')
                        ->placeholder('/ornek-sayfa')
                        ->visible(fn($get) => $get('type') === 'custom'),

                    Select::make('linkable_id')
                        ->label('Kategori Seç')
                        ->options(
                            Category::where('is_active', true)
                                ->pluck('name', 'id')
                        )
                        ->searchable()
                        ->visible(fn($get) => $get('type') === 'category'),

                    Select::make('linkable_id')
                        ->label('Sayfa Seç')
                        ->options(
                            Page::pluck('title', 'id')
                        )
                        ->searchable()
                        ->visible(fn($get) => $get('type') === 'page'),

                    Select::make('target')
                        ->label('Hedef')
                        ->options([
                            '_self'  => 'Aynı Sekme',
                            '_blank' => 'Yeni Sekme',
                        ])
                        ->default('_self'),

                    TextInput::make('sort_order')
                        ->label('Sıra')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),

                    Repeater::make('children')
                        ->label('Alt Menü Öğeleri')
                        ->relationship('children')
                        ->schema([
                            TextInput::make('label')
                                ->label('Başlık')
                                ->required(),

                            Select::make('type')
                                ->label('Tür')
                                ->options([
                                    'custom'   => 'Manuel URL',
                                    'category' => 'Kategori',
                                    'page'     => 'Sayfa',
                                ])
                                ->default('custom')
                                ->live(),

                            TextInput::make('url')
                                ->label('URL')
                                ->placeholder('/ornek-sayfa')
                                ->visible(fn($get) => $get('type') === 'custom'),

                            Select::make('linkable_id')
                                ->label('Kategori Seç')
                                ->options(
                                    Category::where('is_active', true)->pluck('name', 'id')
                                )
                                ->searchable()
                                ->visible(fn($get) => $get('type') === 'category'),

                            Select::make('linkable_id')
                                ->label('Sayfa Seç')
                                ->options(
                                    Page::pluck('title', 'id')
                                )
                                ->searchable()
                                ->visible(fn($get) => $get('type') === 'page'),

                            Select::make('target')
                                ->label('Hedef')
                                ->options([
                                    '_self'  => 'Aynı Sekme',
                                    '_blank' => 'Yeni Sekme',
                                ])
                                ->default('_self'),

                            Toggle::make('is_active')
                                ->label('Aktif')
                                ->default(true),
                        ])
                        ->columns(2)
                        ->collapsible()
                        ->reorderableWithDragAndDrop()
                        ->addActionLabel('Alt Öğe Ekle')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->collapsible()
                ->cloneable()
                ->reorderableWithDragAndDrop()
                ->addActionLabel('Öğe Ekle')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')
                ->label('Menü Adı')
                ->searchable(),
            TextColumn::make('location')
                ->label('Konum')
                ->formatStateUsing(fn($state) => match ($state) {
                    'header'   => 'Header',
                    'footer_1' => 'Footer — Kurumsal',
                    'footer_2' => 'Footer — Yardım',
                    'footer_3' => 'Footer — Kategoriler',
                    default    => $state,
                }),
            TextColumn::make('items_count')
                ->label('Öğe Sayısı')
                ->counts('items'),
            ToggleColumn::make('is_active')
                ->label('Aktif'),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMenus::route('/'),
            'create' => Pages\CreateMenu::route('/create'),
            'edit'   => Pages\EditMenu::route('/{record}/edit'),
        ];
    }
}
