<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Ticket\Schemas;

use AcMarche\GuichetHdv\Enums\ServicesEnum;
use AcMarche\GuichetHdv\Models\Reason;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class TicketForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make([
                    Grid::make(2)->schema([
                        TextInput::make('number')
                            ->label('Numéro')
                            ->required()
                            ->maxLength(255),
                        Select::make('service')
                            ->label('Service')
                            ->options(ServicesEnum::class)
                            ->required(),
                        Textarea::make('reason')
                            ->label('Motif')
                            ->required(),
                        Select::make('suggest_reason')
                            ->label('Suggestion de motif')
                            ->options(
                                fn (): array => Reason::query()->orderBy('content')->pluck('content', 'content')->all()
                            )
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (Set $set, ?string $state) => $set('reason', $state))
                            ->dehydrated(false)
                            ->columnSpan(1),
                    ]),
                ])->heading('Informations'),

                Grid::make(2)->schema([
                    Toggle::make('archive')
                        ->label('Archiver')
                        ->default(false),
                ]),
            ]);
    }
}
