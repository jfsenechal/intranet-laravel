<?php

namespace AcMarche\Security\Tables;

use AcMarche\Security\Filament\Resources\ModuleResource;
use AcMarche\Security\Handler\ModuleHandler;
use AcMarche\Security\Models\Module;
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

    public static function inline(Table $table, Model $ownerRecord): Table
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
                Tables\Actions\CreateAction::make('create')
                    ->label('Ajouter un module')
                    ->icon('tabler-plus')
                    ->action(function (array $data) use ($ownerRecord) {
                        ModuleHandler::addModuleFromUser($ownerRecord, $data);
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }
}
