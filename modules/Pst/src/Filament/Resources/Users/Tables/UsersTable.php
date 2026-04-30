<?php

declare(strict_types=1);

namespace AcMarche\Pst\Filament\Resources\Users\Tables;

use AcMarche\Pst\Filament\Resources\Users\UserResource;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('last_name')
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', [$record]))
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email'),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->icon('tabler-phone')->toggleable(),
                TextColumn::make('extension')
                    ->label('Extension')
                    ->icon('tabler-device-landline-phone')->toggleable(),
                TextColumn::make('roles_list')
                    ->label('Roles')
                    ->state(fn (User $record) => $record->roles()->pluck('name')->join(', '))
                    ->toggleable(),
                TextColumn::make('services.name')
                    ->label('Services')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true), TextColumn::make('services_count')
                    ->label('Nbre de services')
                    ->counts('services')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('departments')
                    ->label('Départements')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('username')
                    ->label('Nom d\'utilisateur')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(fn () => Role::pluck('name', 'id'))
                    ->query(fn (Builder $query, array $data) => $query->when(
                        $data['value'],
                        fn (Builder $query, $roleId) => $query->whereHas(
                            'roles',
                            fn (Builder $q) => $q->where('roles.id', $roleId)
                        )
                    )),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
