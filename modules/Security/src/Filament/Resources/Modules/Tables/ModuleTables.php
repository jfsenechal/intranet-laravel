<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Modules\Tables;

use AcMarche\Security\Filament\Actions\RevokeAction;
use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
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
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé'),
                TextColumn::make('roles.name')
                    ->label('Rôles'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label('Ajouter un module')
                    ->icon('tabler-plus'),
            ])
            ->recordActions([
                EditAction::make(),
                RevokeAction::make()
                    ->action(function (Module $module) use ($ownerRecord): void {
                        ModuleHandler::revokeModuleFromUser($ownerRecord, $module->id);
                    }),
            ])
            ->recordAction(ViewAction::class);
    }
}
