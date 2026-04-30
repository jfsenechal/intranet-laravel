<?php

declare(strict_types=1);

namespace AcMarche\Ad\Filament\Resources\ClassifiedAd\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ClassifiedAdInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('end_date')
                    ->icon('tabler-mail')
                    ->dateTime(),
                TextEntry::make('content')
                    ->label(null)
                    ->hiddenLabel()
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                ImageEntry::make('medias')
                    ->disk('public'),
            ]);
    }
}
