<?php

declare(strict_types=1);

namespace AcMarche\SportsActivities\Filament\Resources\Groups\Tables;

use AcMarche\SportsActivities\Filament\Exports\GroupRegistrationsPdfExport;
use AcMarche\SportsActivities\Filament\Resources\Registrations\Schemas\RegistrationInfoList;
use AcMarche\SportsActivities\Models\Group;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GroupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day')->label('Jour')
                    ->sortable(),
                TextColumn::make('time')->label('Heure')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Lieu')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('age')
                    ->label('Âge'),
                TextColumn::make('price')
                    ->label('Prix')
                    ->money('EUR')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('registrations_count')
                    ->counts('registrations')
                    ->label('Inscriptions'),
            ])
            ->filters([
                SelectFilter::make('activity_id')
                    ->label('Activité')
                    ->relationship('activity', 'name'),
            ])
            ->recordActions([
                Action::make('groupsInfo')
                    ->label('Inscrits')
                    ->icon(Heroicon::UserGroup)
                    ->color('info')
                    ->modalHeading(fn (Group $record): string => 'Inscrits - '.($record->activity?->name ?? $record->day)
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer')
                    ->schema(fn (Schema $schema): Schema => RegistrationInfoList::configure($schema)),
                Action::make('exportPdf')
                    ->label('Exporter en PDF')
                    ->icon(Heroicon::ArrowDownTray)
                    ->color('info')
                    ->action(fn (Group $record): StreamedResponse => GroupRegistrationsPdfExport::download($record)),
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer la selection')
                        ->icon(Heroicon::Trash),
                ]),
            ]);
    }
}
