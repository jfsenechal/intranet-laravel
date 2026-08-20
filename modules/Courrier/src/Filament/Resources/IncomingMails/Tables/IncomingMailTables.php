<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\IncomingMails\Tables;

use AcMarche\Courrier\Filament\Resources\IncomingMails\IncomingMailResource;
use AcMarche\Courrier\Models\Category;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class IncomingMailTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => IncomingMailRepository::scopeToTodayForCurrentUser($query)
            )
            ->defaultSort('mail_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('reference_number')
                    ->searchable()
                    ->label('Référence'),
                TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->sortable()
                    ->label('Date'),
                TextColumn::make('sender')
                    ->searchable()
                    ->label('Expéditeur'),
                TextColumn::make('description')
                    ->searchable()
                    ->label('Description')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        // Only render the tooltip if the column contents exceeds the length limit.
                        return $state;
                    }),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(),
                IconColumn::make('is_notified')
                    ->label('Notifié')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_registered')
                    ->label('Recommandé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('has_acknowledgment')
                    ->label('Accusé de réception')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department')
                    ->label('Département')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function forCategorization(Table $table): Table
    {
        return $table
            ->query(IncomingMailRepository::withoutCategory(...))
            ->defaultSort('mail_date', 'desc')
            ->defaultPaginationPageOption(50)
            ->paginated([25, 50, 100])
            ->emptyStateHeading('Tous les courriers du CPAS ont une catégorie')
            ->emptyStateIcon('tabler-checks')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Numéro')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->label('Date')
                    ->sortable(),
                TextColumn::make('sender')
                    ->label('Expéditeur')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(60),
                SelectColumn::make('category_id')
                    ->label('Catégorie')
                    ->placeholder('À classer')
                    ->options(fn (): array => self::categoryOptions())
                    ->width('16rem'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (IncomingMail $record): string => IncomingMailResource::getUrl(
                        'view',
                        ['record' => $record],
                    )),
            ])
            ->toolbarActions([
                BulkAction::make('setCategory')
                    ->label('Attribuer une catégorie')
                    ->icon('tabler-category')
                    ->schema([
                        Select::make('category_id')
                            ->label('Catégorie')
                            ->options(fn (): array => self::categoryOptions())
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data, HasTable $livewire): void {
                        // Updated one by one rather than with a mass update, so
                        // the saved event still re-indexes each mail.
                        $records->each(
                            fn (IncomingMail $record): bool => $record->update(['category_id' => $data['category_id']]),
                        );

                        $livewire->deselectAllTableRecords();

                        Notification::make()
                            ->title(sprintf('%d courrier(s) classé(s)', $records->count()))
                            ->success()
                            ->send();
                    }),
            ]);
    }

    /**
     * The drafts the AI encoded, waiting to be verified.
     *
     * The row opens the edit form rather than the view page: a draft exists to
     * be corrected, and validating it there walks on to the next one. What the
     * model most often gets wrong or leaves empty — the number, the sender, the
     * routing — is what the columns show, so an obviously bad draft can be
     * spotted and discarded without opening it.
     */
    public static function forDrafts(Table $table): Table
    {
        return $table
            ->query(IncomingMailRepository::drafts(...))
            ->defaultSort('id')
            ->defaultPaginationPageOption(50)
            ->paginated([25, 50, 100])
            ->emptyStateHeading('Aucun brouillon à vérifier')
            ->emptyStateDescription(
                'Les brouillons sont créés depuis la boîte mail, en sélectionnant des messages '
                .'et en lançant l\'analyse par l\'IA.'
            )
            ->emptyStateIcon('tabler-sparkles')
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Numéro')
                    ->placeholder('Non lu')
                    ->searchable(),
                TextColumn::make('sender')
                    ->label('Expéditeur')
                    ->placeholder('Non lu')
                    ->searchable(),
                TextColumn::make('description')
                    ->label('Description')
                    ->placeholder('Non lue')
                    ->searchable()
                    ->limit(60),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->placeholder('À choisir')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('user_add')
                    ->label('Analysé par')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Analysé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department')
                    ->label('Département')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Vérifier')
                    ->icon('tabler-check')
                    ->url(fn (IncomingMail $record): string => IncomingMailResource::getUrl(
                        'edit',
                        ['record' => $record],
                    )),
            ])
            ->recordUrl(fn (IncomingMail $record): string => IncomingMailResource::getUrl(
                'edit',
                ['record' => $record],
            ))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Supprimer les brouillons')
                        // The page is not a resource page, so nothing infers the
                        // policy for it: without this the action refuses to run.
                        ->authorizeIndividualRecords('delete'),
                ]),
            ]);
    }

    public static function forAdvanceSearch(Table $table, Builder $builder): Table
    {
        return $table
            ->query(fn (): Builder => $builder)
            ->emptyStateHeading('Aucun courrier trouvé')
            ->defaultPaginationPageOption(50)
            ->paginated([25, 50, 100])
            ->columns([
                TextColumn::make('reference_number')
                    ->label('Référence'),
                TextColumn::make('id')
                    ->label('Numéro')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mail_date')
                    ->date('d/m/Y')
                    ->label('Date'),
                TextColumn::make('sender')
                    ->label('Expéditeur'),
                TextColumn::make('description')
                    ->label('Description')
                    ->html()
                    ->limit(80)
                    ->toggleable(),
                TextColumn::make('category.name')
                    ->label('Catégorie')
                    ->visible(DepartmentScope::currentUserAdministersCpas(...))
                    ->badge()
                    ->color('gray')
                    ->toggleable(),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('recipients.full_name')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->separator(',')
                    ->limitList(2)
                    ->expandableLimitedList()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_registered')
                    ->label('Recommandé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('department')
                    ->label('Département')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordUrl(fn (IncomingMail $record): string => IncomingMailResource::getUrl('view', ['record' => $record])
            );
    }

    /**
     * @return array<int, string>
     */
    private static function categoryOptions(): array
    {
        return Category::query()->orderBy('name')->pluck('name', 'id')->all();
    }
}
