<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;

describe('User courrierDepartment', function () {
    test('returns VILLE department for ville admin role', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        expect($user->courrierDepartment())->toBe(DepartmentCourrierEnum::VILLE);
    });

    test('returns CPAS department for cpas admin role', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach($role);

        expect($user->courrierDepartment())->toBe(DepartmentCourrierEnum::CPAS);
    });

    test('returns BGM department for bourgmestre admin role', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value]);
        $user->roles()->attach($role);

        expect($user->courrierDepartment())->toBe(DepartmentCourrierEnum::BGM);
    });

    test('returns null for user without courrier roles', function () {
        $user = User::factory()->create();

        expect($user->courrierDepartment())->toBeNull();
    });
});

describe('Department Global Scope', function () {
    test('filters incoming mails by authenticated user department', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        $villeMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);
        $bgmMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::BGM->value]);

        $this->actingAs($user);

        $mails = IncomingMail::all();

        expect($mails)->toHaveCount(1)
            ->and($mails->first()->id)->toBe($villeMail->id);
    });

    test('filters services by authenticated user department', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach($role);

        $villeService = Service::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasService = Service::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);

        $this->actingAs($user);

        $services = Service::all();

        expect($services)->toHaveCount(1)
            ->and($services->first()->id)->toBe($cpasService->id);
    });

    test('user without department sees all records', function () {
        $user = User::factory()->create();

        $villeMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);
        $nullDepartmentMail = IncomingMail::factory()->create(['department' => null]);

        $this->actingAs($user);

        $mails = IncomingMail::all();

        expect($mails)->toHaveCount(3);
    });
});

describe('Department Auto-Assignment', function () {
    test('auto-assigns department when creating record as authenticated user', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $mail = IncomingMail::create([
            'reference_number' => 'TEST-001',
            'sender' => 'Test Sender',
            'mail_date' => now(),
            'user_add' => $user->username,
        ]);

        expect($mail->department)->toBe(DepartmentCourrierEnum::VILLE->value);
    });

    test('does not override explicitly set department', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $mail = IncomingMail::create([
            'reference_number' => 'TEST-002',
            'sender' => 'Test Sender',
            'mail_date' => now(),
            'department' => DepartmentCourrierEnum::CPAS->value,
            'user_add' => $user->username,
        ]);

        expect($mail->department)->toBe(DepartmentCourrierEnum::CPAS->value);
    });
});

describe('Custom Scopes', function () {
    test('forDepartment scope filters by specific department', function () {
        $villeMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);

        $mails = IncomingMail::query()->forDepartment(DepartmentCourrierEnum::CPAS)->get();

        expect($mails)->toHaveCount(1)
            ->and($mails->first()->id)->toBe($cpasMail->id);
    });

    test('allDepartments scope bypasses department filter', function () {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        $villeMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);

        $this->actingAs($user);

        $allMails = IncomingMail::query()->allDepartments()->get();

        expect($allMails)->toHaveCount(2);
    });
});
