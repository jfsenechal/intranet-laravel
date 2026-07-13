<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Pages;

use AcMarche\Hrm\Filament\Widgets\AbsenceRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\ContractRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\DeadlineRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\EmployeeRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\SmsReminderRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\TrainingRemindersWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Gate;
use Override;

final class HrmDashboard extends BaseDashboard
{
    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-home';

    #[Override]
    protected static ?string $navigationLabel = 'Tableau de bord';

    #[Override]
    protected static ?int $navigationSort = -10;

    public static function canAccess(): bool
    {
        return Gate::forUser(auth()->user())->check('hrm-administrator');
    }

    public function getColumns(): int|array
    {
        return 2;
    }

    public function getTitle(): string
    {
        return 'Tableau de bord RH';
    }

    /**
     * @return array<int, class-string>
     */
    public function getWidgets(): array
    {
        return [
            AbsenceRemindersWidget::class,
            DeadlineRemindersWidget::class,
            TrainingRemindersWidget::class,
            ContractRemindersWidget::class,
            EmployeeRemindersWidget::class,
            SmsReminderRemindersWidget::class,
        ];
    }
}
