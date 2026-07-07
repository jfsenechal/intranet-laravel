<?php

declare(strict_types=1);

namespace AcMarche\AldermenAgenda\Filament\Resources\Event\Schemas;

use AcMarche\AldermenAgenda\Enums\EventTypesEnum;
use AcMarche\AldermenAgenda\Enums\OrganizersEnum;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Flex::make([
                    Section::make([
                        TextInput::make('name')
                            ->label('Intitulé')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Radio::make('event_type')
                            ->label('Type d\'événement')
                            ->enum(EventTypesEnum::class)
                            ->options(EventTypesEnum::class)
                            ->required(),
                        Radio::make('organizer')
                            ->label('Organisateur')
                            ->enum(OrganizersEnum::class)
                            ->options(OrganizersEnum::class)
                            ->required(),
                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->columnSpanFull(),
                        FileUpload::make('file1_name')
                            ->label('Pièce jointe 1')
                            ->disk('public')
                            ->directory(config('aldermen-agenda.uploads.files'))
                            ->maxSize(7168)
                            ->nullable(),
                        FileUpload::make('file2_name')
                            ->label('Pièce jointe 2')
                            ->disk('public')
                            ->directory(config('aldermen-agenda.uploads.files'))
                            ->maxSize(7168)
                            ->nullable(),
                    ])
                        ->columns(2),
                    Section::make([
                        DateTimePicker::make('start_at')
                            ->label('Date de début')
                            ->required()
                            ->suffixIcon('tabler-calendar-stats'),
                        DateTimePicker::make('end_at')
                            ->label('Date de fin')
                            ->required()
                            ->suffixIcon('tabler-calendar-stats'),
                        DateTimePicker::make('reminder_at')
                            ->label('Date de rappel')
                            ->nullable()
                            ->suffixIcon('tabler-bell'),
                        TextInput::make('location')
                            ->label('Lieu')
                            ->maxLength(255)
                            ->nullable(),
                        Toggle::make('is_local')
                            ->label('Sur la commune ?'),
                    ])->grow(false),
                ])->from('md'),
            ]);
    }
}
