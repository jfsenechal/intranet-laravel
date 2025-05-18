<?php

namespace AcMarche\Security\Tables;

use AcMarche\Security\Form\ModuleForm;
use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\RoleRepository;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
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
                    ->state(fn(Model|User $record): string => $record->rolesByModule($owner->id)
                        ->pluck('name')->implode(', ')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make('create')
                    ->label('Ajouter un utilisateur')
                    ->icon('tabler-user-plus')
                    ->action(function (array $data) use ($owner) {
                        try {
                            ModuleHandler::addUserFromModule($owner, $data);
                            Notification::make()
                                ->success()
                                ->title('Utilisateur ajouté');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur '.$e->getMessage());
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->fillForm(function (User $record) use ($owner): array {
                        $roles = RoleRepository::findByModuleAndUser($owner, $record);
                        $data['roles'] = $roles->pluck('name')->toArray();

                        return $data;
                    })
                    ->form(fn(Form $form) => ModuleForm::addUserFromModule($form, $owner))
                    ->action(function (array $data, Form $form) use ($owner) {
                        try {
                            ModuleHandler::syncUserRolesForModule($owner, $form->getRecord(), $data);
                            Notification::make()
                                ->success()
                                ->title('Utilisateur ajouté');
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Erreur '.$e->getMessage());
                        }
                    }),
                Tables\Actions\Action::make('revoke')
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
