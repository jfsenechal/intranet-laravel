<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Repository\RecipientRepository;

it('returns recipient options sorted by last name ascending', function (): void {
    Recipient::factory()->create(['last_name' => 'Zimmer', 'first_name' => 'Anna']);
    Recipient::factory()->create(['last_name' => 'Albert', 'first_name' => 'Bruno']);
    Recipient::factory()->create(['last_name' => 'Albert', 'first_name' => 'Alice']);

    $labels = RecipientRepository::getForOptions()->values()->all();

    expect($labels)->toBe([
        'Albert Alice',
        'Albert Bruno',
        'Zimmer Anna',
    ]);
});
