<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Employee;

it('truncates the generated slug to fit the 62-character slug column', function (): void {
    $employee = Employee::factory()->create([
        'last_name' => 'A',
        'first_name' => 'Acces RH Forem Indeed UVCW Cheques Repas PST Acropole Securite Sociale Contacts',
        'slug' => null,
    ]);

    expect(mb_strlen($employee->slug))->toBeLessThanOrEqual(62)
        ->and($employee->slug)->toStartWith('a-acces-rh-forem');
});

it('keeps truncated slugs unique across employees with the same long name', function (): void {
    $longName = str_repeat('Acces Repas Acropole ', 10);

    $first = Employee::factory()->create([
        'last_name' => 'A',
        'first_name' => $longName,
        'slug' => null,
    ]);

    $second = Employee::factory()->create([
        'last_name' => 'A',
        'first_name' => $longName,
        'slug' => null,
    ]);

    expect(mb_strlen($first->slug))->toBeLessThanOrEqual(62)
        ->and(mb_strlen($second->slug))->toBeLessThanOrEqual(62)
        ->and($second->slug)->not->toBe($first->slug);
});
