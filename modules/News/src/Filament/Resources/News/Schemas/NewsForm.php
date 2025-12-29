<?php

namespace AcMarche\News\Filament\Resources\News\Schemas;

use AcMarche\Security\Constant\DepartmentEnum;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

final class NewsForm
{
    public static function configete(Schema $schema): Schema
    {
        return $schema
            ->schema([
                FileUpload::make('featured_image')
                    ->label('Featured Image')
                    ->image()
                    ->disk('public')
                    ->directory('news')
                    ->columnSpanFull(),
            ]);
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Flex::make([
                    Section::make([
                        Forms\Components\TextInput::make('name')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(
                                fn (string $operation, $state, Set $set) => $operation === 'create' ? $set(
                                    'slug',
                                    Str::slug($state)
                                ) : null
                            )
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('content')
                            ->label('Contenu')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('medias')
                            ->label('Pièces jointes')
                            ->required()
                            ->maxFiles(3)
                            ->disk('public')
                            ->directory('uploads/news')
                            // ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            // ->preserveFilenames()
                            ->multiple()
                            ->previewable(false)
                            ->downloadable()
                            ->maxSize(10240),
                    ]),
                    Section::make([
                        Forms\Components\Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\Select::make('department')
                            ->label('Département')
                            ->default(DepartmentEnum::COMMON->value)
                            ->options(DepartmentEnum::class)
                            ->required()
                            ->suffixIcon('tabler-ladder'),
                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date de fin de publication')
                            ->default(Carbon::make('now')->add('2 weeks'))
                            ->required()
                            ->suffixIcon('tabler-calendar-stats'),

                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
