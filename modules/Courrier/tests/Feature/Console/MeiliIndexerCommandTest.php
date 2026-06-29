<?php

declare(strict_types=1);

use Symfony\Component\Console\Command\Command;

it('fails when the given incoming mail id does not exist', function (): void {
    $this->artisan('courrier:meili-indexer', ['--id' => 999999])
        ->expectsOutputToContain('Incoming mail "999999" not found.')
        ->assertExitCode(Command::FAILURE);
});
