<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ListIncomingMails;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

it('only lists the incoming mail of the current day', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $today = IncomingMail::factory()->create(['mail_date' => today()]);
    $yesterday = IncomingMail::factory()->create(['mail_date' => today()->subDay()]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$today])
        ->assertCanNotSeeTableRecords([$yesterday]);
});

it('lets an administrator see every department of the day', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));

    $ville = IncomingMail::factory()->create(['mail_date' => today(), 'department' => DepartmentCourrierEnum::VILLE->value]);
    $cpas = IncomingMail::factory()->create(['mail_date' => today(), 'department' => DepartmentCourrierEnum::CPAS->value]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$ville, $cpas]);
});

it('limits an index user to the mail of their department', function (): void {
    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
    $user->roles()->attach($role);
    $this->actingAs($user);

    $ville = IncomingMail::factory()->create(['mail_date' => today(), 'department' => DepartmentCourrierEnum::VILLE->value]);
    $cpas = IncomingMail::factory()->create(['mail_date' => today(), 'department' => DepartmentCourrierEnum::CPAS->value]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$ville])
        ->assertCanNotSeeTableRecords([$cpas]);
});

it('limits a regular user to the mail they receive', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $mine = IncomingMail::factory()->create(['mail_date' => today()]);
    $mine->recipients()->attach($recipient->id);

    $other = IncomingMail::factory()->create(['mail_date' => today()]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$other]);
});

it('limits a regular user to the mail of their linked service', function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);

    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $service = Service::factory()->create();
    $recipient->services()->attach($service->id);

    $mine = IncomingMail::factory()->create(['mail_date' => today()]);
    $mine->services()->attach($service->id);

    $other = IncomingMail::factory()->create(['mail_date' => today()]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$mine])
        ->assertCanNotSeeTableRecords([$other]);
});

it('shows nothing to a user with no recipient, service or department', function (): void {
    $this->actingAs(User::factory()->create());

    $mail = IncomingMail::factory()->create(['mail_date' => today()]);

    livewire(ListIncomingMails::class)
        ->loadTable()
        ->assertCanNotSeeTableRecords([$mail]);
});
