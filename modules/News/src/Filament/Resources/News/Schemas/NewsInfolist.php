<?php

declare(strict_types=1);

namespace AcMarche\News\Filament\Resources\News\Schemas;

use AcMarche\Security\Enums\DepartmentWithCommonEnum;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class NewsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('end_date')
                    ->icon('tabler-mail')
                    ->dateTime(),
                TextEntry::make('department')
                    ->formatStateUsing(fn ($state) => DepartmentWithCommonEnum::tryFrom($state)?->getLabel() ?? 'Unknown')
                    ->icon(
                        fn ($state) => DepartmentWithCommonEnum::tryFrom($state)?->getIcon() ?? 'heroicon-m-question-mark-circle'
                    )
                    ->color(fn ($state) => DepartmentWithCommonEnum::tryFrom($state)?->getColor() ?? 'gray')
                    ->icon('tabler-mail'),
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
