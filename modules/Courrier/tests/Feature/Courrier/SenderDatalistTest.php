<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Sender;
use AcMarche\Courrier\Repository\SenderRepository;
use AcMarche\Security\Models\Role;
use App\Models\User;

/**
 * Log in as a user administering the given department.
 */
function actingAsSenderAdmin(RolesEnum $role): User
{
    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => $role->value]));
    test()->actingAs($user);

    return $user;
}

/**
 * A saved sender of the department, with one mail from them on the given date.
 */
function senderWithMailOn(DepartmentCourrierEnum $department, string $name, string $mailDate): void
{
    Sender::factory()->create([
        'name' => $name,
        'department' => $department->value,
    ]);

    IncomingMail::factory()->create([
        'department' => $department->value,
        'sender' => $name,
        'mail_date' => $mailDate,
    ]);
}

describe('sender autocomplete suggestions', function (): void {
    test('suggests a sender who wrote recently', function (): void {
        senderWithMailOn(DepartmentCourrierEnum::CPAS, 'Recent SA', now()->subMonths(3)->toDateString());

        actingAsSenderAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        expect(SenderRepository::forDatalist())->toContain('Recent SA');
    });

    test('drops a sender whose last mail is older than two years', function (): void {
        senderWithMailOn(DepartmentCourrierEnum::CPAS, 'Dormant SA', now()->subYears(5)->toDateString());

        actingAsSenderAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        expect(SenderRepository::forDatalist())->not->toContain('Dormant SA');
    });

    test('drops a sender who has never written, however recently saved', function (): void {
        Sender::factory()->create([
            'name' => 'Jamais Écrit SA',
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        actingAsSenderAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        expect(SenderRepository::forDatalist())->not->toContain('Jamais Écrit SA');
    });

    test('does not suggest the senders of another department', function (): void {
        senderWithMailOn(DepartmentCourrierEnum::VILLE, 'Ville SA', now()->subMonth()->toDateString());
        senderWithMailOn(DepartmentCourrierEnum::CPAS, 'Cpas SA', now()->subMonth()->toDateString());

        actingAsSenderAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        expect(SenderRepository::forDatalist())
            ->toContain('Cpas SA')
            ->not->toContain('Ville SA');
    });

    test('suggests a sender saved after the list was already cached', function (): void {
        actingAsSenderAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        expect(SenderRepository::forDatalist())->not->toContain('Nouveau SA');

        senderWithMailOn(DepartmentCourrierEnum::CPAS, 'Nouveau SA', now()->toDateString());

        expect(SenderRepository::forDatalist())->toContain('Nouveau SA');
    });
});
