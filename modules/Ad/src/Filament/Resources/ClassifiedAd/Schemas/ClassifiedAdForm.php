<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Date;

final class ClassifiedAdForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Flex::make([
                    Section::make([
                        TextInput::make('name')
                            ->label('Titre')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->columnSpanFull(),
                        RichEditor::make('content')
                            ->label('Contenu')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('medias')
                            ->label('Pièces jointes')
                            ->required()
                            ->maxFiles(5)
                            ->disk('public')
                            ->directory(config('ad.uploads.medias'))
                            // ->acceptedFileTypes(['image/jpeg', 'image/png', 'application/pdf'])
                            // ->preserveFilenames()
                            ->multiple()
                            ->previewable(false)
                            ->downloadable()
                            ->maxSize(10240),
                    ]),
                    Section::make([
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->relationship('category', 'name')
                            ->required(),
                        DatePicker::make('end_date')
                            ->label('Date de fin de publication')
                            ->default(Date::make('now')->add('2 weeks'))
                            ->required()
                            ->suffixIcon('tabler-calendar-stats'),

                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
