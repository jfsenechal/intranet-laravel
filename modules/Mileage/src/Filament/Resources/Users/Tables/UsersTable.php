<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Users\Tables;

use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Providers\MileageServiceProvider;
use AcMarche\Security\Filament\Actions\RevokeAction;
use AcMarche\Security\Handler\ModuleHandler;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query->whereHas(
                    'roles',
                    fn ($q) => $q->where('module_id', MileageServiceProvider::$module_id)
                )
            )
            ->columns([
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->searchable(),
                TextColumn::make('departments')
                    ->label('Départements')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                RevokeAction::make()
                    ->modalDescription(
                        'Êtes-vous sûr de vouloir révoquer l\'accès de cet agent au module déplacements ? Ses rôles et données personnelles seront supprimés.'
                    )
                    ->action(function (User $record): void {
                        ModuleHandler::revokeModuleFromUser($record, MileageServiceProvider::$module_id);
                        PersonalInformation::where('username', $record->username)->delete();
                    }),
                EditAction::make(),
            ]);
    }
}
