<?php

declare(strict_types=1);

namespace AcMarche\App\Filament\Resources\Articles\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ArticleInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Article')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('body')
                            ->label('Contenu')
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')->label('Modifié le')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
