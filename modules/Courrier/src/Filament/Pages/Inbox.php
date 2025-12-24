<?php

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\Inbox\Tables\InboxTables;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

final class Inbox extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|null|BackedEnum $navigationIcon = 'tabler-inbox';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Boite mail';

    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    protected string $view = 'courrier::filament.pages.inbox';

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();
        if ($user?->isAdministrator()) {
            return true;
        }

        return $user?->hasRole(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value) ?? false;
    }

    public function getTitle(): string|Htmlable
    {
        return 'Boite mail';
    }

    public function table(Table $table): Table
    {
        return InboxTables::configure($table);
    }
}
