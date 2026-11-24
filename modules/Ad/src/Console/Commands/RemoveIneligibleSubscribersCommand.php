<?php

declare(strict_types=1);

namespace AcMarche\Ad\Console\Commands;

use AcMarche\Ad\Services\SubscriptionService;
use Illuminate\Console\Command;
use Override;
use Symfony\Component\Console\Command\Command as SfCommand;

final class RemoveIneligibleSubscribersCommand extends Command
{
    /**
     * @var string
     */
    #[Override]
    protected $signature = 'ad:remove-ineligible-subscribers';

    /**
     * @var string
     */
    #[Override]
    protected $description = 'Remove classified ad subscribers who are no longer an active agent under contract';

    public function handle(SubscriptionService $subscriptionService): int
    {
        $removedEmails = $subscriptionService->removeIneligibleSubscribers();

        foreach ($removedEmails as $email) {
            $this->line("Removed {$email}");
        }

        $this->info('Removed '.count($removedEmails).' ineligible subscriber(s).');

        return SfCommand::SUCCESS;
    }
}
