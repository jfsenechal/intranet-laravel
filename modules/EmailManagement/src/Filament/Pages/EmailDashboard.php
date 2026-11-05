<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Filament\Resources\Citoyens\Widgets\ExpiredAccountsWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;
use Override;

final class EmailDashboard extends BaseDashboard
{
    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    #[Override]
    protected static ?string $navigationLabel = 'Tableau de bord';

    #[Override]
    protected static ?int $navigationSort = -10;

    public static function canAccess(): bool
    {
        return ListEmployePage::canAccess();
    }

    public function getTitle(): string
    {
        return 'Tableau de bord';
    }

    /**
     * @return array<int, class-string>
     */
    public function getWidgets(): array
    {
        return [
            ExpiredAccountsWidget::class,
        ];
    }
}
