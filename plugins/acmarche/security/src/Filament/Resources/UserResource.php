<?php

namespace AcMarche\Security\Filament\Resources;

use AcMarche\Security\Constant\NavigationGroupEnum;
use AcMarche\Security\Filament\Resources\UserResource\Pages;
use AcMarche\Security\Filament\Resources\UserResource\RelationManagers\ModuleRelationManager;
use AcMarche\Security\Form\UserForm;
use AcMarche\Security\Tables\UserTables;
use App\Models\User;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return NavigationGroupEnum::SETTINGS->getLabel();
    }

    public static function getModelLabel(): string
    {
        return 'Agents';
    }

    public static function form(Schema $form): Schema
    {
        return UserForm::createForm($form);
    }

    public static function table(Table $table): Table
    {
        return UserTables::table($table);
    }

    public static function getRelations(): array
    {
        return [
            ModuleRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
