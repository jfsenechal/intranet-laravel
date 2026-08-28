<?php

declare(strict_types=1);

namespace AcMarche\Note\Filament\Resources\Notes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;

final class NoteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make([
                    TextEntry::make('name')
                        ->hiddenLabel()
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large),
                    TextEntry::make('content')
                        ->hiddenLabel()
                        ->html()
                        ->prose()
                        ->columnSpanFull(),
                ]),
                Section::make()
                    ->columns(3)
                    ->components([
                        IconEntry::make('is_encrypted')
                            ->label('Chiffré')
                            ->boolean(),
                        TextEntry::make('user_add')
                            ->label('Auteur')
                            ->icon('tabler-user'),
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->icon('tabler-clock-plus'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i')
                            ->icon('tabler-clock-edit'),
                    ]),
            ]);
    }
}
