<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ProcessInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Procédure')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nom'),
                        TextEntry::make('description')
                            ->label('Description')
                            ->extraAttributes(['class' => 'whitespace-pre-line'])
                            ->placeholder('—'),
                    ]),
                Section::make('Métadonnées')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ]),
            ]);
    }
}
