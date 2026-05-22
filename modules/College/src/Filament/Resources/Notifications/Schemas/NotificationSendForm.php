<?php

declare(strict_types=1);

namespace AcMarche\College\Filament\Resources\Notifications\Schemas;

use AcMarche\College\Enums\NotificationType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

final class NotificationSendForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Type de notification')
                    ->schema([
                        ToggleButtons::make('type')
                            ->label('Type')
                            ->options(NotificationType::class)
                            ->default(NotificationType::Ordre)
                            ->required()
                            ->live()
                            ->inline(),
                    ])
                    ->columnSpanFull(),

                Section::make('Message')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        DatePicker::make('date_college')
                            ->label('Date du Collège')
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        TextInput::make('sujet')
                            ->label('Sujet du mail')
                            ->helperText('Sujet du mail')
                            ->required()
                            ->maxLength(255),
                        RichEditor::make('message')
                            ->label('Contenu du mail')
                            ->helperText('Contenu du mail')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Documents')
                    ->columns(2)
                    ->schema([
                        FileUpload::make('file_college')
                            ->label(fn (Get $get): string => self::type($get)->collegeFileLabel())
                            ->helperText(fn (Get $get): string => self::type($get)->collegeFileHelp())
                            ->disk('local')
                            ->directory('college/notifications')
                            ->visibility('private')
                            ->maxSize(51200)
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => $file->getClientOriginalName(),
                            )
                            ->requiredWithout('file_service'),
                        FileUpload::make('file_service')
                            ->label(fn (Get $get): string => self::type($get)->serviceFileLabel())
                            ->helperText(fn (Get $get): string => self::type($get)->serviceFileHelp())
                            ->disk('local')
                            ->directory('college/notifications')
                            ->visibility('private')
                            ->maxSize(51200)
                            ->getUploadedFileNameForStorageUsing(
                                fn (TemporaryUploadedFile $file): string => $file->getClientOriginalName(),
                            )
                            ->requiredWithout('file_college'),
                    ]),
            ]);
    }

    private static function type(Get $get): NotificationType
    {
        $type = $get('type');

        if ($type instanceof NotificationType) {
            return $type;
        }

        return NotificationType::tryFrom((string) $type) ?? NotificationType::Ordre;
    }
}
