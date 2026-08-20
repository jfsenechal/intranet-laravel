<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\Services\Pages\ListServices;
use AcMarche\Courrier\Filament\Resources\Services\Pages\ViewService;
use AcMarche\Courrier\Filament\Resources\Services\ServiceResource;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

/**
 * Act as a user administering a single department: the service form and the
 * department scope both expect exactly one assignable department.
 */
beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    $this->admin = User::factory()->create();
    $this->admin->roles()->attach(
        Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value])
    );

    $this->actingAs($this->admin);
});

test('deleting a service detaches its courriers without deleting them', function (): void {
    $service = Service::factory()->create();
    $mails = IncomingMail::factory()->count(3)->create();
    $service->incomingMails()->attach($mails->pluck('id'));

    $service->delete();

    expect(Service::query()->whereKey($service->getKey())->exists())->toBeFalse();

    foreach ($mails as $mail) {
        expect(IncomingMail::query()->whereKey($mail->getKey())->exists())->toBeTrue()
            ->and($mail->services()->count())->toBe(0);
    }
});

test('deleting a service detaches its recipients without deleting them', function (): void {
    $service = Service::factory()->create();
    $recipients = Recipient::factory()->count(2)->create();
    $service->recipients()->attach($recipients->pluck('id'));

    $service->delete();

    foreach ($recipients as $recipient) {
        expect(Recipient::query()->whereKey($recipient->getKey())->exists())->toBeTrue()
            ->and($recipient->services()->count())->toBe(0);
    }
});

test('the delete modal warns about the attached courriers', function (): void {
    $service = Service::factory()->create();
    $service->incomingMails()->attach(IncomingMail::factory()->count(3)->create()->pluck('id'));

    livewire(ViewService::class, ['record' => $service->getKey()])
        ->mountAction('delete')
        ->assertMountedActionModalSee('Ce service est lié à 3 courriers.');
});

test('the delete modal warns in the singular for a lone courrier', function (): void {
    $service = Service::factory()->create();
    $service->incomingMails()->attach(IncomingMail::factory()->create());

    livewire(ViewService::class, ['record' => $service->getKey()])
        ->mountAction('delete')
        ->assertMountedActionModalSee('Ce service est lié à 1 courrier.');
});

test('the delete modal keeps the default confirmation when no courrier is attached', function (): void {
    $service = Service::factory()->create();

    livewire(ViewService::class, ['record' => $service->getKey()])
        ->mountAction('delete')
        ->assertMountedActionModalDontSee('Ce service est lié');
});

test('deleting a service from the view page detaches its courriers', function (): void {
    $service = Service::factory()->create();
    $mail = IncomingMail::factory()->create();
    $service->incomingMails()->attach($mail);

    livewire(ViewService::class, ['record' => $service->getKey()])
        ->callAction('delete');

    expect(Service::query()->whereKey($service->getKey())->exists())->toBeFalse()
        ->and(IncomingMail::query()->whereKey($mail->getKey())->exists())->toBeTrue()
        ->and($mail->services()->count())->toBe(0);
});

test('the bulk delete modal warns about the courriers of every selected service', function (): void {
    $first = Service::factory()->create();
    $first->incomingMails()->attach(IncomingMail::factory()->count(2)->create()->pluck('id'));

    $second = Service::factory()->create();
    $second->incomingMails()->attach(IncomingMail::factory()->count(3)->create()->pluck('id'));

    livewire(ListServices::class)
        ->loadTable()
        ->selectTableRecords([$first, $second])
        ->mountAction(TestAction::make('delete')->table()->bulk())
        ->assertMountedActionModalSee('La sélection est liée à 5 courriers.');
});

test('bulk deleting services detaches their courriers', function (): void {
    $first = Service::factory()->create();
    $second = Service::factory()->create();
    $mails = IncomingMail::factory()->count(2)->create();
    $first->incomingMails()->attach($mails->first());
    $second->incomingMails()->attach($mails->last());

    livewire(ListServices::class)
        ->loadTable()
        ->selectTableRecords([$first, $second])
        ->callAction(TestAction::make('delete')->table()->bulk());

    expect(Service::query()->whereKey([$first->getKey(), $second->getKey()])->count())->toBe(0);

    foreach ($mails as $mail) {
        expect(IncomingMail::query()->whereKey($mail->getKey())->exists())->toBeTrue()
            ->and($mail->services()->count())->toBe(0);
    }
});

/**
 * Filament resolves bulk-action authorization through its own helper, which
 * falls back to Response::allow() when the policy method is missing — a plain
 * Gate::allows() check would not catch that. Go through the resource.
 */
test('a user without a courrier admin role is denied the services bulk delete', function (): void {
    $regular = User::factory()->create();
    $regular->roles()->attach(Role::factory()->create(['name' => 'ROLE_GRH_ADMIN']));
    $this->actingAs($regular);

    expect(ServiceResource::can('deleteAny'))->toBeFalse();
});

test('a courrier admin is granted the services bulk delete', function (): void {
    expect(ServiceResource::can('deleteAny'))->toBeTrue();
});
