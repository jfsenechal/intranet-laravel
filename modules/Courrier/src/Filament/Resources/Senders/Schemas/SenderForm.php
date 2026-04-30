<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Senders\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class SenderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->required(),
            ]);
    }
}
