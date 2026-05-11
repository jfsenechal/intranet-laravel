<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Employees\Tables;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Filament\Filters\DirectionFilter;
use AcMarche\Hrm\Filament\Filters\EmployerFilter;
use AcMarche\Hrm\Filament\Filters\ServiceFilter;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\PayScale;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class EmployeeTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('last_name')
            ->defaultPaginationPageOption(50)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'activeContracts.employer',
                'activeContracts.direction',
            ]))
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->limit(70),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable()
                    ->limit(70),
                TextColumn::make('active_functions')
                    ->label('Fonctions')
                    ->state(fn (Employee $record): string => $record->activeContracts
                        ->pluck('job_title')
                        ->filter()
                        ->unique()
                        ->implode(', '))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('active_employers')
                    ->label('Employeurs')
                    ->state(fn (Employee $record): string => $record->activeContracts
                        ->pluck('employer.name')
                        ->filter()
                        ->unique()
                        ->implode(', '))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('active_directions')
                    ->label('Directions')
                    ->state(fn (Employee $record): string => $record->activeContracts
                        ->pluck('direction.name')
                        ->filter()
                        ->unique()
                        ->implode(', '))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        StatusEnum::AGENT->value => 'success',
                        StatusEnum::RETIRED->value => 'info',
                        StatusEnum::TERMINATED->value, StatusEnum::RESIGNED->value, StatusEnum::ENDED->value, StatusEnum::CONTRACT_ENDED->value => 'danger',
                        StatusEnum::APPLICATION->value, StatusEnum::STUDENT->value, StatusEnum::INTERN->value => 'warning',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('hired_at')
                    ->label('Entré')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('private_email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_archived')
                    ->label('Archivé')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filtersFormColumns(2)
            ->persistFiltersInSession()
            ->filters([
                SelectFilter::make('status')
                    ->label('Statut')
                    ->options(StatusEnum::class)
                    ->default(StatusEnum::AGENT->value),
                EmployerFilter::make(),
                SelectFilter::make('pay_scale_id')
                    ->label('Echelle')
                    ->options(fn (): array => PayScale::query()
                        ->with('employer')
                        ->orderBy('employer_id')
                        ->orderBy('name')
                        ->get()
                        ->groupBy(fn (PayScale $payScale): string => $payScale->employer?->name ?? 'Sans employeur')
                        ->map(fn ($group) => $group->pluck('name', 'id')->all())
                        ->all())
                    ->preload(),
                ServiceFilter::make()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $serviceId): Builder => $query->whereHas(
                            'contracts',
                            fn (Builder $query) => $query->where('service_id', $serviceId),
                        ),
                    )),
                DirectionFilter::make()
                    ->query(fn (Builder $query, array $data): Builder => $query->when(
                        $data['value'] ?? null,
                        fn (Builder $query, $directionId): Builder => $query->whereHas(
                            'contracts',
                            fn (Builder $query) => $query->where('direction_id', $directionId),
                        ),
                    )),
                TernaryFilter::make('is_archived')
                    ->label('Archive')
                    ->placeholder('Tous')
                    ->trueLabel('Archives')
                    ->falseLabel('Non archives'),
                TernaryFilter::make('has_active_contract')
                    ->label('Contrat actif')
                    ->placeholder('Tous')
                    ->trueLabel('Avec contrat actif')
                    ->falseLabel('Sans contrat actif')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas(
                            'contracts',
                            fn (Builder $query) => $query->active()
                        ),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave(
                            'contracts',
                            fn (Builder $query) => $query->active()
                        ),
                    ),
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
}
