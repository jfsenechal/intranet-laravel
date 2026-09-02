<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Notes\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class NoteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('client_id'),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Toggle::make('is_done')
                            ->label('Traitée')
                            ->default(false),
                    ]),
            ]);
    }
}
