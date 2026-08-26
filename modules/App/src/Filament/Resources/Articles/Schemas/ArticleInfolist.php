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
                    ->columns(1)
                    ->schema([
                        TextEntry::make('title')->label('Titre'),
                        TextEntry::make('excerpt')->label('Extrait')->placeholder('—'),
                        TextEntry::make('body')->label('Contenu')->html(),
                        TextEntry::make('created_at')->label('Créé le')->dateTime('d/m/Y H:i'),
                        TextEntry::make('updated_at')->label('Modifié le')->dateTime('d/m/Y H:i'),
                    ]),
            ]);
    }
}
