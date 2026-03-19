<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\CreateBlogPost;
use App\Filament\Resources\BlogPosts\Pages\EditBlogPost;
use App\Filament\Resources\BlogPosts\Pages\ListBlogPosts;
use App\Filament\Resources\BlogPosts\Schemas\BlogPostForm;
use App\Filament\Resources\BlogPosts\Tables\BlogPostsTable;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Support\Str;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationLabel = 'Blog Yazıları';
    protected static ?string $modelLabel = 'Blog Yazısı';
    protected static ?string $pluralModelLabel = 'Blog Yazıları';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): string
    {
        return 'İçerik';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->schema([
            Tabs::make()->tabs([

                Tab::make('İçerik')->schema([
                    TextInput::make('title')
                        ->label('Başlık')
                        ->required()
                        ->live(onBlur: true)
                        ->afterStateUpdated(
                            fn(Set $set, ?string $state) =>
                            $set('slug', Str::slug($state ?? ''))
                        )
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->label('Slug')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->columnSpanFull(),

                    FileUpload::make('image')
                        ->label('Kapak Görseli')
                        ->image()
                        ->disk('public')
                        ->directory('blog')
                        ->imageEditor()
                        ->columnSpanFull(),

                    Textarea::make('excerpt')
                        ->label('Özet')
                        ->rows(3)
                        ->columnSpanFull(),

                    RichEditor::make('content')
                        ->label('İçerik')
                        ->columnSpanFull(),
                ]),

                Tab::make('Yayın')->schema([
                    Toggle::make('is_published')
                        ->label('Yayında')
                        ->default(false),

                    DateTimePicker::make('published_at')
                        ->label('Yayın Tarihi')
                        ->default(now()),
                ]),

                Tab::make('SEO')->schema([
                    TextInput::make('meta_title')
                        ->label('Meta Başlık')
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('meta_description')
                        ->label('Meta Açıklama')
                        ->rows(2)
                        ->columnSpanFull(),

                    TextInput::make('meta_keywords')
                        ->label('Anahtar Kelimeler')
                        ->columnSpanFull(),
                ]),

            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Başlık')
                    ->searchable()
                    ->limit(50),

                TextColumn::make('published_at')
                    ->label('Yayın Tarihi')
                    ->dateTime('d.m.Y')
                    ->sortable(),

                ToggleColumn::make('is_published')
                    ->label('Yayında'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('published_at', 'desc');
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
            'index' => ListBlogPosts::route('/'),
            'create' => CreateBlogPost::route('/create'),
            'edit' => EditBlogPost::route('/{record}/edit'),
        ];
    }
}
