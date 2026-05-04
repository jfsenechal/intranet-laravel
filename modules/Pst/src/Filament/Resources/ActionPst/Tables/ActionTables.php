<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\ActionPst\Tables;

use AcMarche\Pst\Enums\ActionScopeEnum;
use AcMarche\Pst\Enums\ActionStateEnum;
use AcMarche\Pst\Enums\ActionSynergyEnum;
use AcMarche\Pst\Enums\ActionTypeEnum;
use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\ActionPstResource;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\Service;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ActionTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('position')
            ->defaultPaginationPageOption(50)
            ->persistFiltersInSession()
            ->columns(self::getColumns())
            ->filters(self::getFilters())
            ->filtersFormColumns(3)
            ->filtersFormWidth(Width::ThreeExtraLarge)
            ->recordAction(ViewAction::class)
            ->recordActions([
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function actionsInline(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->recordUrl(fn(Action $record): string => ActionPstResource::getUrl('view', [$record]))
            ->columns(self::getColumns())
            ->filters([
                //
            ]);
    }

    public static function actionsForWidget(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->recordUrl(fn(Action $record): string => ActionPstResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->numeric()
                    ->label('Id'),
                TextColumn::make('name')
                    ->sortable()
                    ->label('Intitulé')
                    ->limit(95)
                    ->url(fn(Action $record): string => ActionPstResource::getUrl('view', ['record' => $record->id]))
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();

                        if (mb_strlen($state) <= $column->getCharacterLimit()) {
                            return null;
                        }

                        return $state;
                    }),
            ])
            ->filters([
                //
            ]);
    }

    public static function full(Table $table): Table
    {
        $columns = self::getColumns();

        $columns[] = TextColumn::make('state_percentage')
            ->label('Pourcentage d\'avancement')
            ->numeric()
            ->suffix('%')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('roadmap')
            ->label('Feuille de route')
            ->formatStateUsing(fn($state) => $state?->getLabel() ?? '-')
            ->sortable()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('mandataries.last_name')
            ->label('Mandataires')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->formatStateUsing(
                fn($state, Action $record) => $record->mandataries->map(
                    fn($user): string => $user->first_name.' '.$user->last_name
                )->join(', ')
            )
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('users.last_name')
            ->label('Agents pilotes')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->formatStateUsing(
                fn($state, Action $record) => $record->users->map(
                    fn($user): string => $user->first_name.' '.$user->last_name
                )->join(', ')
            )
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('partners.name')
            ->label('Partenaires externes')
            ->listWithLineBreaks()
            ->limitList(2)
            ->expandableLimitedList()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('description')
            ->label('Description')
            ->limit(50)
            ->html()
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('evaluation_indicator')
            ->label('Indicateur d\'évaluation')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('work_plan')
            ->label('Plan de travail')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('budget_estimate')
            ->label('Budget estimé')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('financing_mode')
            ->label('Mode de financement')
            ->limit(50)
            ->toggleable(isToggledHiddenByDefault: true);

        $columns[] = TextColumn::make('validated')
            ->label('Validé')
            ->formatStateUsing(fn($state): string => $state ? 'Oui' : 'Non')
            ->toggleable(isToggledHiddenByDefault: true);

        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->persistFiltersInSession()
            ->recordUrl(fn(Action $record): string => ActionPstResource::getUrl('view', [$record]))
            ->columns($columns)
            ->filters(self::getFilters())
            ->filtersFormColumns(3)
            ->filtersFormWidth(Width::ThreeExtraLarge);
    }

    private static function getFilters(): array
    {
        return [
            SelectFilter::make('operational_objectives')
                ->label('Objectif opérationel')
                ->relationship(
                    'operationalObjective',
                    'name',
                    modifyQueryUsing: fn(Builder $query) => $query
                        ->where(function (Builder $query): void {
                            $query->forSelectedDepartment()
                                ->orWhereNull('department');
                        })
                        ->orderBy('name')
                )
                ->searchable(['name']),
            SelectFilter::make('state')
                ->label('État d\'avancement')
                ->options(
                    collect(ActionStateEnum::cases())
                        ->mapWithKeys(fn(ActionStateEnum $action): array => [$action->value => $action->getLabel()])
                        ->all()
                ),
            SelectFilter::make('type')
                ->label('Type')
                ->options(
                    collect(ActionTypeEnum::cases())
                        ->mapWithKeys(fn(ActionTypeEnum $action): array => [$action->value => $action->getLabel()])
                        ->all()
                ),
            SelectFilter::make('scope')
                ->label('Volet')
                ->options(ActionScopeEnum::class),
            Filter::make('without_odds')
                ->label('Sans ODD')
                ->toggle()
                ->query(fn(Builder $query): Builder => $query->whereDoesntHave('odds')),
            SelectFilter::make('synergy')
                ->label(ActionSynergyEnum::getTitle())
                ->options(ActionSynergyEnum::class),
            SelectFilter::make('users')
                ->label('Agents')
                ->relationship('users', 'last_name')
                //  ->modifyQueryUsing(fn(Builder $query) => $query->orderBy('last_name', 'asc'))
                ->getOptionLabelFromRecordUsing(fn($record): string => $record->first_name.' '.$record->last_name)
                ->searchable(['first_name', 'last_name']),
            SelectFilter::make('services')
                ->label('Services')
                ->options(fn() => Service::query()->orderBy('name')->pluck('name', 'id'))
                ->multiple()
                ->searchable()
                ->query(fn(Builder $query, array $data): Builder => $query->when(
                    $data['values'],
                    fn(Builder $query, array $services): Builder => $query->where(
                        fn(Builder $query) => $query
                            ->whereHas('leaderServices', fn(Builder $q) => $q->whereIn('services.id', $services))
                            ->orWhereHas('partnerServices', fn(Builder $q) => $q->whereIn('services.id', $services))
                    )
                )),
            TrashedFilter::make()
                ->label('Supprimées')
                ->visible(fn(): bool => auth()->user()?->hasOneOfThisRoles([RoleEnum::ADMIN->value]) ?? false),
        ];
    }

    private static function getColumns(): array
    {
        return [
            TextColumn::make('id')
                ->searchable()
                ->sortable()
                ->numeric()
                ->label('Id')
                ->toggleable(),
            TextColumn::make('position')
                ->searchable()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable()
                ->numeric()
                ->label('Numéro'),
            TextColumn::make('oo')
                ->label('Oo')
                ->state(fn(): string => 'Oo')
                ->tooltip(function (TextColumn $column): ?string {
                    $record = $column->getRecord();

                    return $record->operationalObjective?->name;
                })
                ->toggleable(),
            TextColumn::make('name')
                ->searchable()
                ->sortable()
                ->label('Intitulé')
                ->limit(95)
                ->url(fn(Action $record): string => ActionPstResource::getUrl('view', ['record' => $record->id]))
                ->tooltip(function (TextColumn $column): ?string {
                    $state = $column->getState();

                    if (mb_strlen($state) <= $column->getCharacterLimit()) {
                        return null;
                    }

                    return $state;
                }),
            TextColumn::make('state')
                ->label('État d\'avancement')
                ->formatStateUsing(fn(ActionStateEnum $state): string => $state->getLabel() ?? 'Unknown')
                ->toggleable(),
            TextColumn::make('isInternal')
                ->label('Interne')
                ->state(fn(Action $record): string => $record->isInternal() ? 'Oui' : 'Non')
                ->toggleable(),
            TextColumn::make('type')
                ->formatStateUsing(fn(ActionTypeEnum $state): string => $state->getLabel() ?? 'Unknown')
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('synergy')
                ->label(ActionSynergyEnum::getTitle())
                ->formatStateUsing(fn($state) => $state?->getLabel() ?? '-')
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('leaderServices.name')
                ->label('Services porteurs')
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('partnerServices.name')
                ->label('Services partenaires')
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('users.last_name')
                ->label('Agents pilotes')
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList()
                ->formatStateUsing(
                    fn($state, Action $record) => $record->users->map(
                        fn($user): string => $user->first_name.' '.$user->last_name
                    )->join(', ')
                )
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('odds.name')
                ->label('ODD')
                ->listWithLineBreaks()
                ->limitList(2)
                ->expandableLimitedList()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('due_date')
                ->label('Date échéance')
                ->date()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('created_at')
                ->label('Créé le')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('updated_at')
                ->label('Mis à jour le')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }
}
