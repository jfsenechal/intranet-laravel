<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Schemas;

use AcMarche\Hrm\Enums\ReasonsEnum;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AbsenceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ...AbsenceCallouts::components(),
                Section::make('Période')
                    ->columns(2)
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Date de début'),
                        DatePicker::make('end_date')
                            ->label('Date de fin'),
                        DatePicker::make('reminder_date')
                            ->label('Date de rappel'),
                        DatePicker::make('closed_date')
                            ->label('Date de clôture'),
                    ]),
                Section::make('Motif')
                    ->columns(2)
                    ->schema([
                        Select::make('reason')
                            ->label('Raison')
                            ->enum(ReasonsEnum::class)
                            ->options(ReasonsEnum::class),
                        Select::make('ssa')
                            ->label('MEDEX / SSA / Certificat médical / Justificatif reçu ?')
                            ->options([
                                'oui' => 'Oui',
                                'non' => 'Non',
                            ]),
                    ]),
                Section::make('Options')
                    ->columns(3)
                    ->schema([
                        Toggle::make('is_closed')
                            ->label('Cloturé'),
                        Toggle::make('certimed')
                            ->label('Certimed'),
                        Toggle::make('has_resumed')
                            ->label('Reprise'),
                        Toggle::make('clock_updated')
                            ->label('Pointeuse'),
                        Toggle::make('acropole')
                            ->label('Acropole'),
                        Toggle::make('agent_file')
                            ->label('Dossier agent'),
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
