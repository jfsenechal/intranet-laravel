<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Weeks\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class WeekForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('first_day')
                                    ->label('Commence le')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->validationMessages([
                                        'unique' => 'Une semaine commençant à cette date existe déjà.',
                                    ])
                                    ->columnSpan(1),

                                Toggle::make('is_archived')
                                    ->label('Archivée')
                                    ->default(false)
                                    ->columnSpan(1),
                            ]),

                        TagsInput::make('days')
                            ->label('Jours de repas')
                            ->placeholder('Ajouter une date')
                            ->visibleOn('edit')
                            ->columnSpanFull(),

                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
