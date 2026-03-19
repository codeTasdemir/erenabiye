<?php

namespace App\Filament\Resources\Sliders;

use App\Filament\Resources\Sliders\Pages\CreateSlider;
use App\Filament\Resources\Sliders\Pages\EditSlider;
use App\Filament\Resources\Sliders\Pages\ListSliders;
use App\Filament\Resources\Sliders\Schemas\SliderForm;
use App\Filament\Resources\Sliders\Tables\SlidersTable;
use App\Models\Slider;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationLabel = 'Slider';
    protected static ?string $modelLabel = 'Slider';
    protected static ?string $pluralModelLabel = 'Sliderlar';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return 'İçerik';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Slider Bilgileri')
                ->schema([
                    TextInput::make('title')
                        ->label('Başlık')
                        ->maxLength(255),

                    TextInput::make('subtitle')
                        ->label('Alt Başlık')
                        ->maxLength(255),

                    TextInput::make('button_text')
                        ->label('Buton Metni')
                        ->maxLength(100),

                    TextInput::make('button_url')
                        ->label('Buton Linki')
                        ->url()
                        ->maxLength(255),

                    TextInput::make('sort_order')
                        ->label('Sıralama')
                        ->numeric()
                        ->default(0),

                    Toggle::make('is_active')
                        ->label('Aktif')
                        ->default(true),
                ])->columns(2),

            Section::make('Görseller')
                ->schema([
                    FileUpload::make('image')
                        ->label('Masaüstü Görsel (1920x600)')
                        ->image()
                        ->disk('public')
                        ->directory('sliders')
                        ->imageEditor()
                        ->required(),

                    FileUpload::make('mobile_image')
                        ->label('Mobil Görsel (768x400)')
                        ->image()
                        ->disk('public')
                        ->directory('sliders')
                        ->imageEditor(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Görsel'),

                TextColumn::make('title')
                    ->label('Başlık')
                    ->default('—')
                    ->searchable(),

                TextColumn::make('button_text')
                    ->label('Buton')
                    ->default('—'),

                TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif'),
            ])
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('sort_order');
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
            'index' => ListSliders::route('/'),
            'create' => CreateSlider::route('/create'),
            'edit' => EditSlider::route('/{record}/edit'),
        ];
    }
}
