<?php

namespace AcMarche\Document\Filament\Resources\DocumentResource\Schema;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DocumentInfolist
{
public static function configure(Schema $schema): Schema
{
 return $schema
            ->schema([
                TextEntry::make('category')
                    ->label('Catégorie')
                    ->columnSpanFull()
                    ->prose(),
                TextEntry::make('content')
                    ->label(false)
                    ->html()
                    ->columnSpanFull()
                    ->prose(),
                ImageEntry::make('file_name')
                    ->disk('uploads/document'),
            ]);
}
}
