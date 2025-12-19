<?php

namespace AcMarche\Mileage\Filament\Pages;

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Filament\Resources\Declarations\DeclarationResource;
use AcMarche\Mileage\Handler\Calculator;
use AcMarche\Mileage\Models\Declaration;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

final class AllDeclarations extends ListRecords
{
    protected static string $resource = DeclarationResource::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Toutes les déclarations';

    protected static string|null|UnitEnum $navigationGroup = 'Administration';

    /**
     * Force the page to be discovered by the navigation menu.
     */
    public static function isDiscovered(): bool
    {
        return true;
    }

    public static function getNavigationLabel(): string
    {
        return 'Toutes les déclarations';
    }

    public static function getNavigationIcon(): ?string
    {
        return 'tabler-list-check';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();

        return $user?->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value) ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Toutes les déclarations';
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Declaration::query()->with('trips'))
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('user_add')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable()
                    ->url(fn (Declaration $record) => DeclarationResource::getUrl('view', ['record' => $record->id])),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('type_movement')
                    ->label('Type')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->date('d/m/Y')
                    ->sortable(),
                TextColumn::make('trips_count')
                    ->label('Déplacements')
                    ->counts('trips')
                    ->sortable(),
                TextColumn::make('totalKilometers')
                    ->label('Km')
                    ->state(function (Declaration $record): int {
                        $record->loadMissing('trips');
                        $calculator = new Calculator($record);

                        return $calculator->calculate()->totalKilometers;
                    })
                    ->suffix(' km'),
                TextColumn::make('totalRefund')
                    ->label('Remboursement')
                    ->state(function (Declaration $record): float {
                        $record->loadMissing('trips');
                        $calculator = new Calculator($record);

                        return $calculator->calculate()->totalRefund;
                    })
                    ->money('EUR'),
            ])
            ->filters([
                SelectFilter::make('user_add')
                    ->label('Utilisateur')
                    ->options(fn () => Declaration::query()
                        ->distinct()
                        ->pluck('user_add', 'user_add')
                        ->toArray())
                    ->searchable(),
                SelectFilter::make('type_movement')
                    ->label('Type de déplacement')
                    ->options([
                        'interne' => 'Interne',
                        'externe' => 'Externe',
                    ]),
                Filter::make('created_at')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Créé depuis'),
                        DatePicker::make('created_until')
                            ->label('Créé jusqu\'à'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Declaration $record) => DeclarationResource::getUrl('view', ['record' => $record->id])),
            ]);
    }
}
