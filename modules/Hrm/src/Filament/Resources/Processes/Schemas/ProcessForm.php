<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ProcessForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label('Nom')
                    ->maxLength(255)
                    ->required(),
                RichEditor::make('description')
                    ->label('Description')
                    ->helperText('Pour un retour à la ligne MAJ + ENTER')
                    ->extraInputAttributes(['style' => 'min-height: 24rem']),
                FileUpload::make('attachments')
                    ->label('Pièces jointes')
                    ->multiple()
                    ->disk('local')
                    ->directory(config('hrm.uploads.processes'))
                    ->storeFileNamesIn('attachment_names')
                    ->downloadable(),
            ]);
    }
}
