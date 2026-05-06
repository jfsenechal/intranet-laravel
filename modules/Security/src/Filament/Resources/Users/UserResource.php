<?php

declare(strict_types=1);

namespace AcMarche\Security\Filament\Resources\Users;

use AcMarche\Security\Enums\NavigationGroupEnum;
use AcMarche\Security\Filament\Resources\Users\Pages\CreateUser;
use AcMarche\Security\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Security\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Security\Filament\Resources\Users\Pages\ViewUser;
use AcMarche\Security\Filament\Resources\Users\RelationManagers\ModuleRelationManager;
use AcMarche\Security\Filament\Resources\Users\Schemas\UserForm;
use AcMarche\Security\Filament\Resources\Users\Tables\UserTables;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Override;

final class UserResource extends Resource
{
    #[Override]
    protected static ?string $model = User::class;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): string
    {
        return NavigationGroupEnum::SETTINGS->getLabel();
    }

    public static function getModelLabel(): string
    {
        return 'Agents';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserTables::configure($table);
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
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
