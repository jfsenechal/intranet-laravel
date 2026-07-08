<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Filament\Resources\FailedJobs\FailedJobResource;
use App\Filament\Resources\Jobs\JobResource;
use App\Models\FailedJob;
use App\Models\Job;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Override;

final class QueueStatsWidget extends BaseWidget
{
    #[Override]
    protected ?string $pollingInterval = '15s';

    protected static ?int $sort = -1;

    public static function canView(): bool
    {
        return Auth::user()?->isAdministrator() === true;
    }

    #[Override]
    protected function getStats(): array
    {
        $pending = Job::query()->count();
        $failed = FailedJob::query()->count();

        return [
            Stat::make('Jobs en attente', $pending)
                ->description($pending > 0 ? 'En file d\'attente' : 'File vide')
                ->descriptionIcon(Heroicon::OutlinedQueueList)
                ->color($pending > 0 ? 'info' : 'gray')
                ->url(JobResource::getUrl()),
            Stat::make('Jobs échoués', $failed)
                ->description($failed > 0 ? 'À examiner' : 'Aucun échec')
                ->descriptionIcon($failed > 0 ? Heroicon::OutlinedExclamationTriangle : Heroicon::OutlinedCheckCircle)
                ->color($failed > 0 ? 'danger' : 'success')
                ->url(FailedJobResource::getUrl()),
        ];
    }
}
