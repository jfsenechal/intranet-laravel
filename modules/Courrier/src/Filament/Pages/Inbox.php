<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\Inbox\Tables\InboxTables;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

final class Inbox extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-inbox';

    #[Override]
    protected static ?int $navigationSort = 2;

    #[Override]
    protected static ?string $navigationLabel = 'Boite mail';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    #[Override]
    protected string $view = 'courrier::filament.pages.inbox';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return false;
        }

        if ($user->isAdministrator()) {
            return true;
        }

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value,
            RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value,
            RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value,
        ]);
    }

    public function getTitle(): string
    {
        $email = self::resolveDepartment()?->imapEmail();

        return 'Boite mail'.($email !== null ? ' '.$email : '');
    }

    public function table(Table $table): Table
    {
        return InboxTables::configure($table, self::resolveDepartment()?->imapMailbox());
    }

    /**
     * Resolve the courrier department whose mailbox the current user should see.
     *
     * Uses the user's first viewable department; a global administrator with no
     * courrier department falls back to the Ville mailbox.
     */
    private static function resolveDepartment(): ?DepartmentCourrierEnum
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return null;
        }

        $departments = $user->getCourrierViewableDepartments();
        if ($departments !== []) {
            return $departments[0];
        }

        return $user->isAdministrator() ? DepartmentCourrierEnum::VILLE : null;
    }
}
