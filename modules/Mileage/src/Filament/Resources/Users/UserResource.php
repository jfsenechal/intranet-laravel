<?php

namespace AcMarche\Mileage\Filament\Resources\Users;

use AcMarche\Mileage\Filament\Resources\Users\Pages\CreateUser;
use AcMarche\Mileage\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Mileage\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Mileage\Filament\Resources\Users\Schemas\UserForm;
use AcMarche\Mileage\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|null|\UnitEnum $navigationGroup = 'Paramètres';
    protected static ?int $navigationSort = 4;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return 'Liste des agents';
    }

    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
