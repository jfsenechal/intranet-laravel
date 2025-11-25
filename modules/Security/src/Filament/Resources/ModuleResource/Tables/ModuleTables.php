<?php

namespace AcMarche\Security\Filament\Resources\ModuleResource\Tables;

use AcMarche\Security\Filament\Resources\ModuleResource;
use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ModuleTables
{
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé')
                    ->url(fn(Module $record) => ModuleResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('is_public')
                    ->label('Public'),
                Tables\Columns\TextColumn::make('is_external')
                    ->label('Externe'),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Intitulé')
                    ->url(fn(Module $record) => ModuleResource::getUrl('view', ['record' => $record->id])),
                Tables\Columns\TextColumn::make('roles.name')
                    ->label('Rôles'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make('create')
                    ->label('Ajouter un new module')
                    ->icon('tabler-plus')
                    ->action(function (array $data) use ($ownerRecord) {
                        ModuleHandler::addModuleFromUser($ownerRecord, $data);
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('revoke')
                    ->label('Révoquer')
                    ->icon('tabler-user-minus')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Module $module) use ($ownerRecord) {
                        ModuleHandler::revokeUser($module, $ownerRecord);
                    }),
            ]);
    }
}
