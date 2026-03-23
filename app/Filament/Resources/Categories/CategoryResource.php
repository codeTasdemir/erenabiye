<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\CategoriesList;
use App\Filament\Resources\Categories\Schemas\CategoryForm;
use App\Filament\Resources\Categories\Tables\CategoriesTable;
use App\Models\Category;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Actions\Action;



class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Kategoriler';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategoriler';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Temel Bilgiler')
                ->schema([
                    Select::make('parent_id')
                        ->label('Üst Kategori')
                        ->options(function () {
                            return Category::all()->mapWithKeys(function ($category) {
                                $prefix = str_repeat('— ', $category->depth ?? 0);
                                return [$category->id => $prefix . $category->name];
                            });
                        })
                        ->searchable()
                        ->placeholder('Ana Kategori (üst kategori yok)')
                        ->nullable(),

                    TextInput::make('name')
                        ->label('Kategori Adı')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn(Set $set, ?string $state) =>
                            $set('slug', Str::slug($state ?? ''))
                        ),

                    TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->maxLength(255),

                    Textarea::make('description')
                        ->label('Açıklama')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(2),

            Section::make('Görsel')
                ->schema([
                    FileUpload::make('image')
                        ->label('Kategori Görseli')
                        ->image()
                        ->disk('public')
                        ->directory('categories')
                        ->imageEditor()
                        ->columnSpanFull(),
                ]),

            Section::make('SEO')
                ->schema([
                    TextInput::make('meta_title')
                        ->label('Meta Başlık')
                        ->maxLength(255),

                    TextInput::make('meta_keywords')
                        ->label('Meta Anahtar Kelimeler')
                        ->maxLength(255),

                    Textarea::make('meta_description')
                        ->label('Meta Açıklama')
                        ->rows(2)
                        ->maxLength(500)
                        ->columnSpanFull(),
                ])->columns(2)->collapsed(),

            Section::make('Ayarlar')
                ->schema([
                    TextInput::make('sort_order')
                        ->label('Sıralama')
                        ->numeric()
                        ->default(0),

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
                Tables\Columns\TextColumn::make('name')
                    ->label('Kategori Adı')
                    ->searchable()
                    ->sortable(),

               /*  Tables\Columns\TextColumn::make('parent.name')
                    ->label('Üst Kategori')
                    ->default('—')
                    ->sortable(), */
                Tables\Columns\TextColumn::make('full_path')
                    ->label('Tam Yol')
                    ->getStateUsing(fn($record) => $record->full_path)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Ürün Sayısı')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Durum')
                    ->trueLabel('Aktif')
                    ->falseLabel('Pasif'),

               /*  Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Üst Kategori')
                    ->options(Category::whereNull('parent_id')->pluck('name', 'id')), */
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
    }
    
    public static function getListRecordsPageClass(): string
    {
        return CategoriesList::class;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
