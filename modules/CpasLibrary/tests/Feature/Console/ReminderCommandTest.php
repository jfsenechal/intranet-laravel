<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\RolesEnum;
use AcMarche\CpasLibrary\Mail\ReminderMail;
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

it('sends the reminder digest for fiches whose reminder date is today', function (): void {
    $today = Fiche::factory()->create(['date_rappel' => Carbon::today()]);
    Fiche::factory()->create(['date_rappel' => Carbon::today()->addDay()]);
    Fiche::factory()->create(['date_rappel' => null]);

    $this->artisan('cpas-library:reminder')->assertSuccessful();

    Mail::assertSent(ReminderMail::class, function (ReminderMail $mail) use ($today): bool {
        return $mail->hasTo('member@example.com')
            && ! $mail->hasTo('admin@example.com')
            && $mail->fiches->contains($today)
            && $mail->fiches->count() === 1
            && $mail->urls[$today->id] !== '';
    });
    Mail::assertSent(ReminderMail::class, fn (ReminderMail $mail): bool => $mail->hasTo('admin@example.com'));
    Mail::assertNotSent(ReminderMail::class, fn (ReminderMail $mail): bool => $mail->hasTo('stranger@example.com'));
    Mail::assertSentCount(2);
});

it('does not send anything when there are no reminders today', function (): void {
    Fiche::factory()->create(['date_rappel' => Carbon::today()->addDay()]);

    $this->artisan('cpas-library:reminder')->assertSuccessful();

    Mail::assertNothingSent();
});

it('does not send anything when no user holds a library role', function (): void {
    $this->member->roles()->detach();
    $this->admin->roles()->detach();
    Fiche::factory()->create(['date_rappel' => Carbon::today()]);

    $this->artisan('cpas-library:reminder')->assertSuccessful();

    Mail::assertNothingSent();
});
