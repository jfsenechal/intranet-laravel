<?php

declare(strict_types=1);

namespace AcMarche\Pst\Observers;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\TracksHistoryTrait;

final class ActionObserver
{
    use TracksHistoryTrait;

    /**
     * Handle the Action "created" event.
     */
    public function created(Action $action): void
    {
        if ($action->department === DepartmentEnum::VILLE->value) {
            $email = config('pst')['validator']['email'];
        }
    }

    /**
     * Handle the Action "updated" event.
     */
    public function updated(Action $action): void
    {
        $this->track($action);
    }

    /**
     * Handle the Action "deleted" event.
     */
    public function deleted(): void
    {
        // ...
    }

    /**
     * Handle the Action "restored" event.
     */
    public function restored(): void
    {
        // ...
    }

    /**
     * Handle the Action "forceDeleted" event.
     */
    public function forceDeleted(): void
    {
        // ...
    }
}
