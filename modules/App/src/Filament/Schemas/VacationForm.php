<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class VacationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('subject')
                    ->label('Sujet')
                    ->required(),
                Textarea::make('message')
                    ->label('Message')
                    ->required(),
            ]);
    }
}
