<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Http\Middleware\EnsureEmailAdmin;
use AcMarche\EmailManagement\Models\Employe;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

function emailAdmin(): User
{
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_EMAIL_ADMIN->value]);
    $user = User::factory()->create(['is_administrator' => false]);
    $user->roles()->attach($role);

    return $user;
}

function passesEmailAdminMiddleware(?User $user): bool
{
    $request = Request::create('/email-management');
    $request->setUserResolver(fn (): ?User => $user);

    try {
        (new EnsureEmailAdmin)->handle($request, fn (): Symfony\Component\HttpFoundation\Response => response('ok'));

        return true;
    } catch (HttpException) {
        return false;
    }
}

describe('panel access', function (): void {
    it('grants panel access to a user with ROLE_EMAIL_ADMIN', function (): void {
        expect(passesEmailAdminMiddleware(emailAdmin()))->toBeTrue();
    });

    it('grants panel access to a super administrator', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);

        expect(passesEmailAdminMiddleware($user))->toBeTrue();
    });

    it('denies panel access to an authenticated user without the role', function (): void {
        $user = User::factory()->create(['is_administrator' => false]);

        expect(passesEmailAdminMiddleware($user))->toBeFalse();
    });

    it('denies panel access to a guest', function (): void {
        expect(passesEmailAdminMiddleware(null))->toBeFalse();
    });

    it('denies panel access to a user holding an unrelated role', function (): void {
        $user = User::factory()->create(['is_administrator' => false]);
        $user->roles()->attach(Role::factory()->create(['name' => 'ROLE_GRH_ADMIN']));

        expect(passesEmailAdminMiddleware($user))->toBeFalse();
    });
});

describe('EmployePolicy', function (): void {
    beforeEach(function (): void {
        $this->employe = new Employe;
    });

    it('grants every ability to a ROLE_EMAIL_ADMIN user', function (): void {
        $user = emailAdmin();

        expect($user->can('viewAny', Employe::class))->toBeTrue()
            ->and($user->can('view', $this->employe))->toBeTrue()
            ->and($user->can('create', Employe::class))->toBeTrue()
            ->and($user->can('update', $this->employe))->toBeTrue()
            ->and($user->can('delete', $this->employe))->toBeTrue();
    });

    it('grants every ability to a super administrator', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);

        expect($user->can('viewAny', Employe::class))->toBeTrue()
            ->and($user->can('delete', $this->employe))->toBeTrue();
    });

    it('denies every ability to a user without the role', function (): void {
        $user = User::factory()->create(['is_administrator' => false]);

        expect($user->can('viewAny', Employe::class))->toBeFalse()
            ->and($user->can('view', $this->employe))->toBeFalse()
            ->and($user->can('create', Employe::class))->toBeFalse()
            ->and($user->can('update', $this->employe))->toBeFalse()
            ->and($user->can('delete', $this->employe))->toBeFalse();
    });

    it('never allows restore or forceDelete, even for a super administrator', function (): void {
        $user = User::factory()->create(['is_administrator' => true]);

        expect($user->can('restore', $this->employe))->toBeFalse()
            ->and($user->can('forceDelete', $this->employe))->toBeFalse();
    });
});
