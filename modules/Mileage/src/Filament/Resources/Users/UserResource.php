<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Resources\Users;

use AcMarche\Mileage\Filament\Resources\Users\Pages\CreateUser;
use AcMarche\Mileage\Filament\Resources\Users\Pages\EditUser;
use AcMarche\Mileage\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Mileage\Filament\Resources\Users\Schemas\UserForm;
use AcMarche\Mileage\Filament\Resources\Users\Tables\UsersTable;
use AcMarche\Mileage\Policies\UserPolicy;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

final class UserResource extends Resource
{
    #[Override]
    protected static ?string $model = User::class;

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Administration';

    #[Override]
    protected static ?int $navigationSort = 7;

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-user-group';
    }

    public static function getNavigationLabel(): string
    {
        return 'Liste des agents';
    }

    /**
     * The resource is reserved to the mileage administrators.
     *
     * Authorization is delegated to `UserPolicy` from here instead of through
     * the gate: the model is `App\Models\User`, which lives outside the module,
     * so Laravel cannot discover a policy in `AcMarche\Mileage\Policies` for it,
     * and registering one globally would also govern the Security and Pst user
     * resources, which manage the very same model.
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();

        return $user instanceof User && self::policy()->viewAny($user);
    }

    public static function canView(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User && self::policy()->view($user);
    }

    public static function canCreate(): bool
    {
        $user = Auth::user();

        return $user instanceof User && self::policy()->create($user);
    }

    public static function canEdit(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User && self::policy()->update($user);
    }

    public static function canDelete(Model $record): bool
    {
        $user = Auth::user();

        return $user instanceof User && self::policy()->delete($user);
    }

    public static function canDeleteAny(): bool
    {
        $user = Auth::user();

        return $user instanceof User && self::policy()->delete($user);
    }

    public static function canRestore(Model $record): bool
    {
        return self::policy()->restore();
    }

    public static function canRestoreAny(): bool
    {
        return self::policy()->restore();
    }

    public static function canForceDelete(Model $record): bool
    {
        return self::policy()->forceDelete();
    }

    public static function canForceDeleteAny(): bool
    {
        return self::policy()->forceDelete();
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

    private static function policy(): UserPolicy
    {
        return new UserPolicy();
    }
}
