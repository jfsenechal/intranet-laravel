<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Filament\Resources\Absence\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Hidden::make('client_id'),
                        Grid::make(2)
                            ->schema([
                                DatePicker::make('start_date')
                                    ->label('Du')
                                    ->required()
                                    ->default(now())
                                    ->columnSpan(1),

                                DatePicker::make('end_date')
                                    ->label('Au')
                                    ->required()
                                    ->afterOrEqual('start_date')
                                    ->columnSpan(1),
                            ]),
                    ]),
            ]);
    }
}
