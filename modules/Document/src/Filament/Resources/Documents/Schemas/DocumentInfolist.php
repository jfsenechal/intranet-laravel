<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Documents\Schemas;

use AcMarche\App\Filament\Schema\Infolists\PdfViewerEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Schema;

final class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('category.name')
                    ->label('Catégorie')
                    ->badge()
                    ->columnSpanFull(),
                TextEntry::make('content')
                    ->label('Description')
                    ->hiddenLabel()
                    ->html()
                    ->columnSpanFull()
                    ->prose()
                    ->extraAttributes(['class' => 'prose-lg'])
                    ->hidden(fn ($state): bool => blank($state)),
                Flex::make([
                    TextEntry::make('file_size')
                        ->label('Taille')
                        ->formatStateUsing(fn ($state): string => $state ? number_format($state / 1024, 2).' KB' : '-')
                        ->grow(false),
                    TextEntry::make('file_mime')
                        ->label('Type')
                        ->grow(false),
                ])
                    ->columnSpanFull(),
                PdfViewerEntry::make('file_path')
                    ->label('Aperçu')
                    ->minHeight('80svh')
                    ->columnSpanFull()
                    ->disk('public'),
            ]);
    }
}
