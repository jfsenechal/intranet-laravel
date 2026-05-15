<?php

declare(strict_types=1);

namespace AcMarche\GuichetHdv\Filament\Resources\Reason\Schemas;

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
            ]);
    }
}
