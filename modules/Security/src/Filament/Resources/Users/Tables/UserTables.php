<?php

namespace AcMarche\Security\Filament\Resources\Users\Tables;

use AcMarche\Security\Filament\Resources\Modules\Schemas\ModuleForm;
use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\RoleRepository;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class UserTables
{
    public static function configure(Table $table): Table
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
                    ->icon('tabler-phone')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('extension')
                    ->label('Extension')
                    ->icon('tabler-device-landline-phone'),
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
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function inline(Table $table, Model|Module $owner): Table
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
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles')
                    ->state(fn (Model|User $record): string => $record->rolesByModule($owner->id)
                        ->pluck('name')->implode(', ')),
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label('Ajouter un utilisateur')
                    ->icon('tabler-user-plus')
                    ->action(function (array $data) use ($owner) {
                        try {
                            ModuleHandler::addUserFromModule($owner, $data);
                            Notification::make()
                                ->success()
                                ->title('Utilisateur ajouté');
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur '.$e->getMessage());
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->fillForm(function (User $record) use ($owner): array {
                        $roles = RoleRepository::findByModuleAndUser($owner, $record);
                        $data['roles'] = $roles->pluck('name')->toArray();

                        return $data;
                    })
                    ->schema(fn (Schema $schema) => ModuleForm::addUserFromModule($schema, $owner))
                    ->action(function (array $data, Schema $schema) use ($owner) {
                        try {
                            ModuleHandler::syncUserRolesForModule($owner, $schema->getRecord(), $data);
                            Notification::make()
                                ->success()
                                ->title('Utilisateur ajouté');
                        } catch (Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur '.$e->getMessage());
                        }
                    }),
                Action::make('revoke')
                    ->label('Révoquer')
                    ->icon('tabler-user-minus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $user) use ($owner) {
                        ModuleHandler::revokeUser($owner, $user);
                    }),
            ]);
    }
}
