<?php

namespace AcMarche\News\Filament\Resources\NewsResource\Schemas;

use AcMarche\Security\Constant\DepartmentEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class NewsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('end_date')
                    ->icon('tabler-mail')
                    ->dateTime(),
                TextEntry::make('department')
                    ->formatStateUsing(fn($state) => DepartmentEnum::tryFrom($state)?->getLabel() ?? 'Unknown')
                    ->icon(
                        fn($state) => DepartmentEnum::tryFrom($state)?->getIcon() ?? 'heroicon-m-question-mark-circle'
                    )
                    ->color(fn($state) => DepartmentEnum::tryFrom($state)?->getColor() ?? 'gray')
                    ->icon('tabler-mail'),
                TextEntry::make('content')
                    ->label(false)
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                ImageEntry::make('medias')
                    ->disk('public'),
            ]);
    }

}
