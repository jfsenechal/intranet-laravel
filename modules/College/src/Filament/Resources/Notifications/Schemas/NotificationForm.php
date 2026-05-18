<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Http\UploadedFile;

final class NotificationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Document')
                    ->schema([
                        FileUpload::make('file_upload')
                            ->label('Fichier')
                            ->disk('local')
                            ->directory('college/notifications')
                            ->visibility('private')
                            ->maxSize(51200)
                            ->dehydrated(false)
                            ->afterStateUpdated(function ($state, Set $set): void {
                                if (! $state instanceof UploadedFile) {
                                    return;
                                }
                                $set('file_name', $state->getClientOriginalName());
                                $set('mime', $state->getMimeType());
                            })
                            ->columnSpanFull(),

                        Grid::make(2)->schema([
                            TextInput::make('file_name')
                                ->label('Nom du fichier')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('mime')
                                ->label('Type MIME')
                                ->required()
                                ->maxLength(255),
                        ]),
                    ]),
            ]);
    }
}
