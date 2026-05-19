<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class ClassifiedAdInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('content')
                    ->label(null)
                    ->hiddenLabel()
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                ViewEntry::make('medias')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->view('ad::filament.components.media-gallery'),
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('end_date')
                            ->label('Date de fin de publication')
                            ->icon('tabler-mail')
                            ->dateTime(),
                        TextEntry::make('user_add')
                            ->label('Ajouté par'),
                    ]),
            ]);
    }
}
