<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;

describe('RolesEnum department mapping', function (): void {
    test('maps admin roles to correct departments', function (): void {
        expect(RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->getDepartment())
            ->toBe(DepartmentCourrierEnum::BGM)
            ->and(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->getDepartment())
            ->toBe(DepartmentCourrierEnum::VILLE)
            ->and(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->getDepartment())
            ->toBe(DepartmentCourrierEnum::CPAS);
    });

    test('non-admin roles return null department', function (): void {
        expect(RolesEnum::ROLE_INDICATEUR_VILLE->getDepartment())->toBeNull()
            ->and(RolesEnum::ROLE_INDICATEUR_CPAS_READ->getDepartment())->toBeNull()
            ->and(RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->getDepartment())->toBeNull();
    });

    test('getAdminRoles returns only admin roles', function (): void {
        $adminRoles = RolesEnum::getAdminRoles();

        expect($adminRoles)->toHaveCount(3)
            ->and($adminRoles)->toContain(RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN)
            ->and($adminRoles)->toContain(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN)
            ->and($adminRoles)->toContain(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);
    });
});

describe('UserCourrierTrait getCourrierDepartments', function (): void {
    test('returns VILLE department for ville admin role', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        $departments = $user->getCourrierDepartments();

        expect($departments)->toHaveCount(1)
            ->and($departments[0])->toBe(DepartmentCourrierEnum::VILLE);
    });

    test('returns CPAS department for cpas admin role', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach($role);

        $departments = $user->getCourrierDepartments();

        expect($departments)->toHaveCount(1)
            ->and($departments[0])->toBe(DepartmentCourrierEnum::CPAS);
    });

    test('returns BGM department for bourgmestre admin role', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value]);
        $user->roles()->attach($role);

        $departments = $user->getCourrierDepartments();

        expect($departments)->toHaveCount(1)
            ->and($departments[0])->toBe(DepartmentCourrierEnum::BGM);
    });

    test('returns multiple departments for user with multiple admin roles', function (): void {
        $user = User::factory()->create();
        $villeRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $cpasRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach([$villeRole->id, $cpasRole->id]);

        $departments = $user->getCourrierDepartments();

        expect($departments)->toHaveCount(2)
            ->and($departments)->toContain(DepartmentCourrierEnum::VILLE)
            ->and($departments)->toContain(DepartmentCourrierEnum::CPAS);
    });

    test('returns empty array for user without admin courrier roles', function (): void {
        $user = User::factory()->create();

        expect($user->getCourrierDepartments())->toBeEmpty();
    });

    test('non-admin courrier roles do not produce departments', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_READ->value]);
        $user->roles()->attach($role);

        expect($user->getCourrierDepartments())->toBeEmpty();
    });
});

describe('Department Global Scope on IncomingMail', function (): void {
    test('filters incoming mails by authenticated user department', function (): void {
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

    test('user with multiple admin roles sees mails from all their departments', function (): void {
        $user = User::factory()->create();
        $villeRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $cpasRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach([$villeRole->id, $cpasRole->id]);

        $villeMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);
        $bgmMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::BGM->value]);

        $this->actingAs($user);

        $mails = IncomingMail::all();

        expect($mails)->toHaveCount(2)
            ->and($mails->pluck('id')->all())->toContain($villeMail->id)
            ->and($mails->pluck('id')->all())->toContain($cpasMail->id);
    });

    test('user without department sees all records', function (): void {
        $user = User::factory()->create();

        $villeMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasMail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);
        $nullDepartmentMail = IncomingMail::factory()->create(['department' => null]);

        $this->actingAs($user);

        $mails = IncomingMail::all();

        expect($mails)->toHaveCount(3);
    });
});

describe('Department Global Scope on Service', function (): void {
    test('filters services by authenticated user department', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach($role);

        Service::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
        $cpasService = Service::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);

        $this->actingAs($user);

        $services = Service::all();

        expect($services)->toHaveCount(1)
            ->and($services->first()->id)->toBe($cpasService->id);
    });
});

describe('Department Auto-Assignment on IncomingMail', function (): void {
    test('auto-assigns department when user has single admin role', function (): void {
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

    test('does not auto-assign department when user has multiple admin roles', function (): void {
        $user = User::factory()->create();
        $villeRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $cpasRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach([$villeRole->id, $cpasRole->id]);

        $this->actingAs($user);

        $mail = IncomingMail::create([
            'reference_number' => 'TEST-002',
            'sender' => 'Test Sender',
            'mail_date' => now(),
            'user_add' => $user->username,
        ]);

        expect($mail->department)->toBeNull();
    });

    test('does not override explicitly set department', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $mail = IncomingMail::create([
            'reference_number' => 'TEST-003',
            'sender' => 'Test Sender',
            'mail_date' => now(),
            'department' => DepartmentCourrierEnum::CPAS->value,
            'user_add' => $user->username,
        ]);

        expect($mail->department)->toBe(DepartmentCourrierEnum::CPAS->value);
    });
});

describe('Department Auto-Assignment on Service', function (): void {
    test('auto-assigns department when user has single admin role', function (): void {
        $user = User::factory()->create();
        $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value]);
        $user->roles()->attach($role);

        $this->actingAs($user);

        $service = Service::create([
            'name' => 'Test Service',
            'slugname' => 'test-service',
        ]);

        expect($service->department)->toBe(DepartmentCourrierEnum::CPAS->value);
    });

    test('does not auto-assign department when user has multiple admin roles', function (): void {
        $user = User::factory()->create();
        $villeRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
        $bgmRole = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_ADMIN->value]);
        $user->roles()->attach([$villeRole->id, $bgmRole->id]);

        $this->actingAs($user);

        $service = Service::create([
            'name' => 'Test Service 2',
            'slugname' => 'test-service-2',
        ]);

        expect($service->department)->toBeNull();
    });
});
