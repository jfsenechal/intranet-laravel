<?php

declare(strict_types=1);

namespace AcMarche\Offenses\Filament\Resources\Offenders\Schemas;

use AcMarche\Offenses\Filament\Resources\Offenses\OffenseResource;
use AcMarche\Offenses\Models\Offense;
use Filament\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

final class OffenderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Coordonnées')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('street')
                            ->label('Rue')
                            ->placeholder('—'),
                        TextEntry::make('postal_code')
                            ->label('Code postal')
                            ->placeholder('—'),
                        TextEntry::make('city')
                            ->label('Localité')
                            ->placeholder('—'),
                        TextEntry::make('birth_date')
                            ->label('Date de naissance')
                            ->date('d/m/Y')
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),
                Section::make('Incivilités')
                    ->schema([
                        RepeatableEntry::make('offenses')
                            ->hiddenLabel()
                            ->schema([
                                Grid::make(2)
                                    ->columnSpan(2)
                                    ->schema([
                                        TextEntry::make('offenseAct.name')
                                            ->label('Acte')
                                            ->url(
                                                fn (Model $record): string => OffenseResource::getUrl(
                                                    'view',
                                                    ['record' => $record->id]
                                                )
                                            ),
                                        TextEntry::make('fine_amount')
                                            ->label('Amende')
                                            ->money('EUR')
                                            ->placeholder('—'),
                                        IconEntry::make('mediation'),
                                        TextEntry::make('decision_date')
                                            ->label('Date de décision')
                                            ->date('d/m/Y'),
                                    ]),
                                TextEntry::make('id')
                                    ->hiddenLabel()
                                    ->columnSpan(1)
                                    ->formatStateUsing(fn (): string => '')
                                    ->afterContent([
                                        Action::make('editOffense')
                                            ->label('Modifier')
                                            ->icon(Heroicon::Pencil)
                                            ->size('sm')
                                            ->authorize('update', Offense::class)
                                            ->url(
                                                fn (Offense $record): string => OffenseResource::getUrl(
                                                    'edit',
                                                    ['record' => $record->id]
                                                )
                                            ),
                                        Action::make('deleteOffense')
                                            ->label('Supprimer')
                                            ->icon(Heroicon::Trash)
                                            ->color('danger')
                                            ->size('sm')
                                            ->authorize('delete', Offense::class)
                                            ->requiresConfirmation()
                                            ->modalHeading('Supprimer l\'incivilité')
                                            ->modalDescription('Êtes-vous sûr de vouloir supprimer cette incivilité ? Cette action est irréversible.')
                                            ->action(function (Offense $record, ViewRecord $livewire): void {
                                                $record->delete();
                                                $livewire->getRecord()->unsetRelation('offenses');

                                                Notification::make()
                                                    ->title('Incivilité supprimée')
                                                    ->success()
                                                    ->send();
                                            }),
                                    ]),
                            ])
                            ->columns(3)
                            ->grid(1),
                    ]),
            ]);
    }
}
