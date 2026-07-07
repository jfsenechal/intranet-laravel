<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;

Schedule::command('agent:prune-profiles')->daily();
Schedule::command('intranet:sync-users')->daily();
Schedule::command('meal-delivery:prune-absences')->daily();
Schedule::command('hrm:expire-new-hires')->daily();
Schedule::command('courrier:sync')->daily();
Schedule::command('courrier:meili-indexer')->dailyAt('03:00')->withoutOverlapping();
Schedule::command('cpas-library:reminder')->dailyAt('01:00');
Schedule::command('cpas-library:remove-expired')->dailyAt('01:00');
Schedule::command('cpas-library:resume')->weeklyOn(1, '01:00');
foreach (DepartmentEnum::cases() as $department) {
    Schedule::command('hrm:reminders '.mb_strtolower($department->value))->daily();
}
