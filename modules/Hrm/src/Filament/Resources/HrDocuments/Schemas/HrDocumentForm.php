<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HrDocuments\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;

final class HrDocumentForm
{
    public static function getSchema(): array
    {
        return [
            TextInput::make('name')
                ->label('Intitulé')
                ->required()
                ->maxLength(255),
            FileUpload::make('file_name')
                ->label('Fichier')
                ->disk('public')
                ->visibility('public')
                ->directory(config('hrm.uploads.documents'))
                ->required(),
            RichEditor::make('notes')
                ->label('Remarques')
                ->columnSpanFull(),
        ];

    }
}
