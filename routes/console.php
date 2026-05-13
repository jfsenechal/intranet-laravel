<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;
use Illuminate\Support\Facades\Artisan;

Schedule::command('agent:prune-profiles')->daily();
Schedule::command('intranet:sync-users')->daily();
Schedule::command('meal-delivery:prune-absences')->daily();
foreach (DepartmentEnum::cases() as $department) {
    Schedule::command('hrm:reminders '.mb_strtolower($department->value))->daily();
}
