<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;


class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel   = 'Müşteriler';
    protected static ?string $modelLabel        = 'Müşteri';
    protected static ?string $pluralModelLabel  = 'Müşteriler';
    protected static ?int    $navigationSort    = 1;

    public static function getNavigationGroup(): string
    {
        return 'Sistem';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([

                Tab::make('Kişisel Bilgiler')->schema([
                    TextInput::make('name')
                        ->label('Ad Soyad')
                        ->required()
                        ->columnSpanFull(),

                    TextInput::make('email')
                        ->label('E-posta')
                        ->email()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    TextInput::make('password')
                        ->label('Şifre')
                        ->password()
                        ->dehydrateStateUsing(fn($state) => filled($state) ? bcrypt($state) : null)
                        ->dehydrated(fn($state) => filled($state))
                        ->nullable()
                        ->columnSpanFull(),

                    Toggle::make('is_admin')
                        ->label('Admin Yetkisi')
                        ->default(false),
                ])->columns(2),

                Tab::make('Adresler')->schema([
                    Repeater::make('addresses')
                        ->label('Adresler')
                        ->relationship('addresses')
                        ->schema([
                            TextInput::make('title')
                                ->label('Adres Başlığı')
                                ->placeholder('Ev, İş...')
                                ->required(),

                            TextInput::make('first_name')
                                ->label('Ad')
                                ->required(),

                            TextInput::make('last_name')
                                ->label('Soyad')
                                ->required(),

                            TextInput::make('phone')
                                ->label('Telefon')
                                ->tel()
                                ->required(),

                            TextInput::make('city')
                                ->label('Şehir')
                                ->required(),

                            TextInput::make('district')
                                ->label('İlçe')
                                ->required(),

                            TextInput::make('address')
                                ->label('Açık Adres')
                                ->required()
                                ->columnSpanFull(),

                            TextInput::make('zip_code')
                                ->label('Posta Kodu'),

                            Toggle::make('is_default')
                                ->label('Varsayılan Adres')
                                ->default(false),
                        ])
                        ->columns(2)
                        ->columnSpanFull()
                        ->addActionLabel('+ Adres Ekle')
                        ->collapsible()
                        ->collapsed()
                        ->itemLabel(
                            fn(array $state) => ($state['title'] ?? 'Adres') . ' — ' .
                                ($state['first_name'] ?? '') . ' ' .
                                ($state['last_name'] ?? '')
                        ),
                ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Ad Soyad')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('addresses_count')
                    ->label('Adres')
                    ->counts('addresses')
                    ->sortable(),

                TextColumn::make('orders_count')
                    ->label('Sipariş')
                    ->counts('orders')
                    ->sortable(),

                IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Kayıt Tarihi')
                    ->dateTime('d.m.Y')
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\TernaryFilter::make('is_admin')
                    ->label('Admin')
                    ->trueLabel('Adminler')
                    ->falseLabel('Müşteriler'),
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
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\OrdersRelationManager::class,
        ];
    }
}
