<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Pages;

use AcMarche\Courrier\Filament\Actions\AnalyzeAttachmentAction;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Tables\IncomingMailTables;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Override;
use UnitEnum;

/**
 * Every courrier the AI encoded from the mailbox that nobody has verified yet.
 *
 * Drafts are deliberately absent from the day's listing, the notifications and
 * the index until they are validated, so without this page the mail announcing
 * a batch is the only way back to them.
 */
final class DraftIncomingMails extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'tabler-sparkles';

    #[Override]
    protected static ?int $navigationSort = 5;

    #[Override]
    protected static ?string $navigationLabel = 'Brouillons IA';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Courrier';

    #[Override]
    protected string $view = 'courrier::filament.pages.draft-incoming-mails';

    /**
     * Only the drafts of the administrator's own department are listed anyway
     * (the model's DepartmentScope sees to that), so the gate is the AI trial
     * itself — the same one that puts the analysis button on the mailbox.
     */
    public static function canAccess(array $parameters = []): bool
    {
        return AnalyzeAttachmentAction::isUnderTrialFor();
    }

    /**
     * How much is left to verify, so the page is only worth opening when it has
     * something to show.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = IncomingMailRepository::drafts()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function getTitle(): string
    {
        return 'Brouillons IA à vérifier';
    }

    public function getSubheading(): ?string
    {
        return 'Ces courriers ont été encodés par l\'IA et n\'ont pas encore été relus. '
            .'Ils restent invisibles et ne sont pas notifiés tant qu\'ils ne sont pas validés.';
    }

    public function table(Table $table): Table
    {
        return IncomingMailTables::forDrafts($table);
    }
}
