<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Pages;

use AcMarche\Hrm\Filament\Widgets\AbsenceRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\ContractRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\EmployeeRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\LastAbsencesWidget;
use AcMarche\Hrm\Filament\Widgets\SmsReminderRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\TrainingRemindersWidget;
use AcMarche\Hrm\Filament\Widgets\UpcomingDeadlinesWidget;
use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Override;

final class HrmDashboard extends BaseDashboard
{
    #[Override]
    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-home';

    #[Override]
    protected static ?string $navigationLabel = 'Tableau de bord';

    #[Override]
    protected static ?int $navigationSort = -10;

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
            LastAbsencesWidget::class,
            UpcomingDeadlinesWidget::class,
            TrainingRemindersWidget::class,
            ContractRemindersWidget::class,
            EmployeeRemindersWidget::class,
            AbsenceRemindersWidget::class,
            SmsReminderRemindersWidget::class,
        ];
    }
}
