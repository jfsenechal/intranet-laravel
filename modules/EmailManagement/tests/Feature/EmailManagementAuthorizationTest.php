<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Http\Middleware\EnsureEmailAdmin;
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
