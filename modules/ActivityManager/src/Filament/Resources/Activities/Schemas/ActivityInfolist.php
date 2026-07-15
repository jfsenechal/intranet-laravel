<?php

declare(strict_types=1);

namespace AcMarche\ActivityManager\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class ActivityInfolist
{
    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('description')
                    ->label('Description')
                    ->hiddenLabel()
                    ->columnSpanFull()
                    ->placeholder('—'),
            ]);
    }
}
