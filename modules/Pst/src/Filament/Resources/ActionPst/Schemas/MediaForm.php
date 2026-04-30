<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class MediaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(
            [
                Hidden::make('mime_type'),
                Hidden::make('size'),
                TextInput::make('name')
                    ->label('Nom du média')
                    ->required()
                    ->maxLength(150),
                FileUpload::make('file_name')
                    ->label('Pièce jointe')
                    ->required()
                    ->maxFiles(1)
                    ->disk('public')
                    ->directory(config('pst.uploads.medias'))
                    ->downloadable()
                    ->maxSize(10240)
                    ->afterStateUpdated(function ($state, Set $set): void {
                        if ($state instanceof TemporaryUploadedFile) {
                            $set('mime_type', $state->getMimeType());
                            $set('size', $state->getSize());
                        }
                    }),
            ]
        );
    }
}
