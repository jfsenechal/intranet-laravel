<?php

declare(strict_types=1);

namespace AcMarche\Mediation\Filament\Resources\AgreementTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class AgreementTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextInput::make('name')
                    ->label('Nom')
                    ->required()
                    ->maxLength(255),

                TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(70)
                    ->unique(ignoreRecord: true),
            ]);
    }
}
