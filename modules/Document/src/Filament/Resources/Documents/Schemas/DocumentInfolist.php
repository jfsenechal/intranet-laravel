<?php

declare(strict_types=1);

namespace AcMarche\Document\Filament\Resources\Documents\Schemas;

use AcMarche\App\Filament\Schemas\Infolist\PdfViewerEntry;
use AcMarche\Document\Models\Document;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

final class DocumentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                TextEntry::make('category.name')
                    ->label('Catégorie')
                    ->badge(),
                TextEntry::make('content')
                    ->label('Description')
                    ->hiddenLabel()
                    ->html()
                    ->columnSpanFull()
                    ->prose()
                    ->extraAttributes(['class' => 'prose-lg'])
                    ->hidden(fn ($state): bool => blank($state)),
                PdfViewerEntry::make('file_name')
                    ->label('Aperçu')
                    ->state(fn (Document $record): ?string => $record->filePathOnDisk())
                    ->minHeight('80svh')
                    ->columnSpanFull()
                    ->disk('public'),
            ]);
    }
}
