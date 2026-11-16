<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Mail\ResumeMail;
use AcMarche\CpasLibrary\Models\Fiche;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();

    $this->member = User::factory()->create(['email' => 'member@example.com']);
    $this->member->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY->value]));

    $this->admin = User::factory()->create(['email' => 'admin@example.com']);
    $this->admin->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_LIBRARY_ADMIN->value]));

    $this->stranger = User::factory()->create(['email' => 'stranger@example.com']);
});

it('sends the weekly digest for fiches added over the last seven days', function (): void {
    $recent = Fiche::factory()->create(['createdAt' => Carbon::today()->subDays(2)]);
    Fiche::factory()->create(['createdAt' => Carbon::today()->subDays(30)]);

    $this->artisan('cpas-library:resume')->assertSuccessful();

    Mail::assertSent(ResumeMail::class, function (ResumeMail $mail) use ($recent): bool {
        return $mail->hasTo('member@example.com')
            && ! $mail->hasTo('admin@example.com')
            && $mail->fiches->contains($recent)
            && $mail->fiches->count() === 1
            && $mail->urls[$recent->id] !== '';
    });
    Mail::assertSent(ResumeMail::class, fn (ResumeMail $mail): bool => $mail->hasTo('admin@example.com'));
    Mail::assertNotSent(ResumeMail::class, fn (ResumeMail $mail): bool => $mail->hasTo('stranger@example.com'));
    Mail::assertSentCount(2);
});

it('does not send anything when no fiches were added in the last seven days', function (): void {
    Fiche::factory()->create(['createdAt' => Carbon::today()->subDays(30)]);

    $this->artisan('cpas-library:resume')->assertSuccessful();

    Mail::assertNothingSent();
});

it('does not send anything when no user holds a library role', function (): void {
    $this->member->roles()->detach();
    $this->admin->roles()->detach();
    Fiche::factory()->create(['createdAt' => Carbon::today()]);

    $this->artisan('cpas-library:resume')->assertSuccessful();

    Mail::assertNothingSent();
});
