<?php

namespace AcMarche\Security\Tables;

use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserTables
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(50)
            ->defaultSort('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Téléphone')
                    ->icon('tabler-phone'),
                Tables\Columns\TextColumn::make('extension')
                    ->label('Extension')
                    ->icon('tabler-device-landline-phone'),
                Tables\Columns\TextColumn::make('roles.name'),
                Tables\Columns\TextColumn::make('username')
                    ->label('Nom d\'utilisateur')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function inline(Table $table, Model|Module $owner): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) => $query->whereHas(
                    'roles',
                    fn($roleQuery) => $roleQuery->whereHas(
                        'module',
                        fn($moduleQuery) => $moduleQuery->where('modules.id', $owner->id)
                    )
                )
            )
            ->defaultPaginationPageOption(50)
            ->defaultSort('last_name')
            ->columns([
                Tables\Columns\TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('create')
                    ->label('Ajouter un utilisateur')
                    ->icon('tabler-user-plus')
                    ->action(function (array $data) use ($owner) {
                        try {
                            ModuleHandler::addUser($data);
                            Notification::make()
                                ->success()
                                ->title('Utilisateur ajouté');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur '.$e->getMessage());
                        }
                        //redirect()->route('acmarche.security.users.create')),
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

}
