<?php

namespace AcMarche\Mileage\Filament\Resources\Rates\Schemas;

use AcMarche\Mileage\Handler\RateHandler;
use AcMarche\Mileage\Models\Rate;
use Closure;
use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class RateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('amount')
                            ->label('Montant (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\TextInput::make('omnium')
                            ->label('Omnium (€/km)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€'),

                        Forms\Components\DatePicker::make('start_date')
                            ->label('Date début')
                            ->required()
                            ->before('end_date')
                            ->live(onBlur: true)
                            ->rules([
                                fn (Get $get, ?Rate $record): Closure => function (
                                    string $attribute,
                                    $value,
                                    Closure $fail
                                ) use ($get, $record): void {
                                    if (! $value) {
                                        return;
                                    }

                                    $endDate = $get('end_date');
                                    if (! $endDate) {
                                        return;
                                    }

                                    if (RateHandler::hasOverlappingRate($value, $endDate, $record)) {
                                        $fail('Cette période chevauche une période de tarif existante.');
                                    }
                                },
                            ]),

                        Forms\Components\DatePicker::make('end_date')
                            ->label('Date fin')
                            ->required()
                            ->after('start_date')
                            ->live(onBlur: true)
                            ->rules([
                                fn (Get $get, ?Rate $record): Closure => function (
                                    string $attribute,
                                    $value,
                                    Closure $fail
                                ) use ($get, $record): void {
                                    if (! $value) {
                                        return;
                                    }

                                    $startDate = $get('start_date');
                                    if (! $startDate) {
                                        return;
                                    }

                                    if (RateHandler::hasOverlappingRate($startDate, $value, $record)) {
                                        $fail('Cette période chevauche une période de tarif existante.');
                                    }
                                },
                            ]),
                    ])
                    ->columns(2),
            ]);
    }
}
