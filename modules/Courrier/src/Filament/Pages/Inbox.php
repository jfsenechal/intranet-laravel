<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\Inbox\Tables\InboxTables;
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
        if ($user?->isAdministrator()) {
            return true;
        }

        return $user?->hasRole(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value) ?? false;
    }

    public function getTitle(): string
    {
        return 'Boite mail '.config('courrier.imap.ville.email');
    }

    public function table(Table $table): Table
    {
        return InboxTables::configure($table);
    }
}
