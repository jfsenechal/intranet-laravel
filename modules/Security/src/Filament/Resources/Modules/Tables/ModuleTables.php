<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Tables;

use AcMarche\Security\Filament\Actions\RevokeAction;
use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use AcMarche\Security\Repository\ModuleRepository;
use App\Models\User;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

final class ModuleTables
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé'),
                IconColumn::make('is_public')
                    ->label('Accessible à tous')
                    ->icon(fn (bool $state): ?Heroicon => $state ? Heroicon::CheckCircle : null)
                    ->falseIcon(false)
                    ->color('success')
                    ->toggleable(),
                IconColumn::make('is_external')
                    ->label('Url externe')
                    ->icon(fn (bool $state): ?Heroicon => $state ? Heroicon::CheckCircle : null)
                    ->color('success')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('description')
                    ->label('Description')
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->recordAction(ViewAction::class)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function inline(Table $table, User|Model $ownerRecord): Table
    {
        $roleNamesByModule = $ownerRecord instanceof User
            ? $ownerRecord->roles()->get()->groupBy('module_id')->map(
                fn ($roles): string => $roles->pluck('name')->implode(', ')
            )
            : collect();

        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé'),
                TextColumn::make('user_roles')
                    ->label('Rôles attribués')
                    ->state(fn (Module $record): string => $roleNamesByModule->get($record->id, '')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label('Attribuer un module')
                    ->icon('tabler-plus')
                    ->using(function (array $data) use ($ownerRecord): ?Module {
                        if (! $ownerRecord instanceof User) {
                            return null;
                        }

                        $moduleId = (int) $data['module'];
                        $data['roles'] = (array) ($data['roles'] ?? []);
                        ModuleHandler::addModuleFromUser($ownerRecord, $moduleId, $data);

                        return ModuleRepository::find($moduleId);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->using(function (Module $record, array $data) use ($ownerRecord): Module {
                        if ($ownerRecord instanceof User) {
                            $data['roles'] = (array) ($data['roles'] ?? []);
                            ModuleHandler::syncUserRolesForModule($record, $ownerRecord, $data);
                        }

                        return $record;
                    }),
                RevokeAction::make()
                    ->action(function (Module $module) use ($ownerRecord): void {
                        ModuleHandler::revokeModuleFromUser($ownerRecord, $module->id);
                    }),
            ])
            ->recordAction(EditAction::class);
    }
}
