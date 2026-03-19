<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\Pages\CreateSetting;
use App\Filament\Resources\Settings\Pages\EditSetting;
use App\Filament\Resources\Settings\Pages\ListSettings;
use App\Filament\Resources\Settings\Schemas\SettingForm;
use App\Filament\Resources\Settings\Tables\SettingsTable;
use App\Models\Setting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;

class SettingResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Setting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'key';
    protected static ?string $navigationLabel = 'Ayarlar';
    protected static ?string $modelLabel = 'Ayar';
    protected static ?string $pluralModelLabel = 'Ayarlar';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string
    {
        return 'Sistem';
    }


    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('key')
                ->label('Anahtar')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

            Select::make('group')
                ->label('Grup')
                ->options([
                    'general'  => 'Genel',
                    'contact'  => 'İletişim',
                    'social'   => 'Sosyal Medya',
                    'payment'  => 'Ödeme',
                    'shipping' => 'Kargo',
                    'seo'      => 'SEO',
                    'email'    => 'E-posta',
                ])
                ->default('general')
                ->required(),

            Textarea::make('value')
                ->label('Değer')
                ->rows(3)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Anahtar')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('group')
                    ->label('Grup')
                    ->badge()
                    ->formatStateUsing(fn($state) => match ($state) {
                        'general'  => 'Genel',
                        'contact'  => 'İletişim',
                        'social'   => 'Sosyal Medya',
                        'payment'  => 'Ödeme',
                        'shipping' => 'Kargo',
                        'seo'      => 'SEO',
                        'email'    => 'E-posta',
                        default    => $state,
                    }),

                TextColumn::make('value')
                    ->label('Değer')
                    ->limit(60)
                    ->default('—'),
            ])
            ->filters([
                SelectFilter::make('group')
                    ->label('Grup')
                    ->options([
                        'general'  => 'Genel',
                        'contact'  => 'İletişim',
                        'social'   => 'Sosyal Medya',
                        'payment'  => 'Ödeme',
                        'shipping' => 'Kargo',
                        'seo'      => 'SEO',
                        'email'    => 'E-posta',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
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
            'index' => ListSettings::route('/'),
            'create' => CreateSetting::route('/create'),
            'edit' => EditSetting::route('/{record}/edit'),
        ];
    }
}
