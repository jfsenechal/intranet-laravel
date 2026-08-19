<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Filament\Resources\IncomingMails\Tables\IncomingMailTables;
use AcMarche\Courrier\Repository\DepartmentScope;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Override;
use UnitEnum;

/**
 * Catch-up page for the mail encoded before the category existed, or saved
 * without one: it lists the CPAS mail still missing a category and lets the
 * CPAS administrators set it straight from the row.
 */
final class CategorizeIncomingMails extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-category';

    #[Override]
    protected static ?int $navigationSort = 4;

    #[Override]
    protected static ?string $navigationLabel = 'Courriers sans catégorie';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    #[Override]
    protected string $view = 'courrier::filament.pages.categorize-incoming-mails';

    public static function canAccess(array $parameters = []): bool
    {
        return DepartmentScope::currentUserAdministersCpas();
    }

    /**
     * Surface how much is left to classify, so the page is only worth opening
     * when it has something to show.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = IncomingMailRepository::withoutCategory()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function getTitle(): string
    {
        return 'Courriers sans catégorie';
    }

    public function table(Table $table): Table
    {
        return IncomingMailTables::forCategorization($table);
    }
}
