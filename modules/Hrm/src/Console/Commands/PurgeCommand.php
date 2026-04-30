<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Console\Commands;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Mail\PurgedApplicationsMail;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Console\Command\Command as SfCommand;

final class PurgeCommand extends Command
{
    protected $signature = 'hrm:purge';

    protected $description = 'Purge candidates without applications received in the last year';

    public function handle(): int
    {
        $recipients = (array) config('hrm.team_emails', []);

        if ($recipients === []) {
            $this->error('No HRM team emails configured (hrm.team_emails).');

            return SfCommand::FAILURE;
        }

        $oneYearAgo = Carbon::now()->subYear()->startOfDay();

        $candidates = Employee::query()
            ->where('status', StatusEnum::APPLICATION)
            ->whereDoesntHave('applications', function (Builder $query) use ($oneYearAgo): void {
                $query->where('received_at', '>=', $oneYearAgo);
            })
            ->orderBy('last_name')
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No stale candidates to purge.');

            return SfCommand::SUCCESS;
        }

        Mail::to($recipients)->send(new PurgedApplicationsMail($candidates));

        $candidates->each(fn (Employee $candidate) => $candidate->delete());

        $this->info("Purged {$candidates->count()} candidate(s).");

        return SfCommand::SUCCESS;
    }
}
