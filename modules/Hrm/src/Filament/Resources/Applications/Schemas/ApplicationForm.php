<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Applications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

final class ApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Candidature')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('received_at')
                            ->label('Date de réception')
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        Select::make('employer_id')
                            ->label('Employeur')
                            ->relationship('employer', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('job_function_id')
                            ->label('Fonction sollicitée')
                            ->relationship('jobFunction', 'name')
                            ->searchable()
                            ->preload(),
                        TextInput::make('mail_reference')
                            ->label('Référence courrier')
                            ->maxLength(255),
                    ]),
                Fieldset::make("Type d'appel")
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_spontaneous')
                            ->label('Candidature spontanée'),
                        Toggle::make('is_public_call')
                            ->label('Appel public')
                            ->live(),
                        Toggle::make('is_priority')
                            ->label('Prioritaire'),
                        TextInput::make('public_call')
                            ->label("Nom de l'appel public")
                            ->maxLength(150)
                            ->visible(fn (Get $get): bool => (bool) $get('is_public_call'))
                            ->columnSpanFull(),
                    ]),
                Section::make('Fichier')
                    ->schema([
                        FileUpload::make('file')
                            ->label('Fichier de candidature')
                            ->disk('public')
                            ->directory(config('hrm.uploads.candidates'))
                            ->visibility('public')
                            ->columnSpanFull(),
                    ]),
                Section::make('Notes')
                    ->schema([
                        RichEditor::make('notes')
                            ->label('Notes')
                            ->hiddenLabel()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
