<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Reason\Schemas;

use AcMarche\GuichetHdv\Enums\ServicesEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ReasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                TextInput::make('content')
                    ->label('Motif')
                    ->required()
                    ->maxLength(255),
                Select::make('service')
                    ->label('Service')
                    ->options(ServicesEnum::class)
                    ->helperText('Laisser vide pour proposer ce motif dans tous les services.'),
            ]);
    }
}
