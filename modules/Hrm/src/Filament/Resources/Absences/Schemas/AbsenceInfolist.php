<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class AbsenceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ...AbsenceCallouts::components(),
                Flex::make([
                    Section::make(
                        self::content()
                    ),
                    Section::make(
                        self::side()
                    )
                        ->grow(false),
                ])
                    ->from('md'),
            ]);
    }

    private static function content(): array
    {
        return [
            Fieldset::make('Période')
                ->columns(4)
                ->schema([
                    TextEntry::make('start_date')
                        ->label('Date de début')
                        ->date('d/m/Y'),
                    TextEntry::make('end_date')
                        ->label('Date de fin')
                        ->date('d/m/Y'),
                    TextEntry::make('reminder_date')
                        ->label('Date de rappel')
                        ->date('d/m/Y'),
                    TextEntry::make('closed_date')
                        ->label('Date de clôture')
                        ->date('d/m/Y'),
                ]),
            Fieldset::make('Motif')
                ->columns(2)
                ->schema([
                    TextEntry::make('reason')
                        ->label('Raison'),
                    TextEntry::make('ssa')
                        ->label('SSA'),
                    TextEntry::make('work_capacity_assessment')
                        ->label('Évaluation de la capacité de travail'),
                ]),
            TextEntry::make('notes')
                ->label('Notes')
                ->hiddenLabel()
                ->html()
                ->prose()
                ->columnSpanFull(),
        ];
    }

    private static function side(): array
    {
        return [
            Section::make('Options')
                ->columns(2)
                ->schema([
                    IconEntry::make('is_closed')
                        ->label('Clôturé')
                        ->boolean(),
                    IconEntry::make('has_resumed')
                        ->label('Reprise')
                        ->boolean(),
                    IconEntry::make('clock_updated')
                        ->label('Pointeuse')
                        ->boolean(),
                    IconEntry::make('acropole')
                        ->label('Acropole')
                        ->boolean(),
                    IconEntry::make('agent_file')
                        ->label('Dossier agent')
                        ->boolean(),
                    IconEntry::make('certimed')
                        ->label('Certimed')
                        ->boolean(),
                ]),
            Section::make('Métadonnées')
                ->columns(2)
                ->schema([
                    TextEntry::make('created_at')
                        ->label('Créé le')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('user_add')
                        ->label('Par')
                        ->placeholder('—'),
                    TextEntry::make('updated_at')
                        ->label('Modifié le')
                        ->dateTime('d/m/Y H:i')
                        ->placeholder('—'),
                    TextEntry::make('updated_by')
                        ->label('Par')
                        ->placeholder('—'),
                ]),
        ];
    }
}
