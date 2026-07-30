<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Jobs\IndexIncomingMailJob;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
    Storage::fake(config('courrier.storage.disk'));

    $user = User::factory()->create();
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    $user->roles()->attach($role);
    $this->actingAs($user);
});

it('re-indexes a new mail once its services and recipients are attached', function (): void {
    $service = Service::factory()->create();
    $recipient = Recipient::factory()->create();

    Queue::fake();

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'reference_number' => 'TEST-INDEX-1',
            'mail_date' => today(),
            'sender' => 'ACME',
            'attachment_file' => UploadedFile::fake()->create('rapport.pdf', 10, 'application/pdf'),
            'primary_services' => [$service->id],
            'primary_recipients' => [$recipient->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $mail = IncomingMail::query()->where('reference_number', 'TEST-INDEX-1')->firstOrFail();

    // Once for the `created` event, once after the pivots exist. Only the
    // second job can index the mail with its recipients and services.
    Queue::assertPushed(
        IndexIncomingMailJob::class,
        fn (IndexIncomingMailJob $job): bool => $job->incomingMailId === $mail->id,
    );
    expect($mail->services->pluck('id'))->toContain($service->id);
    expect($mail->recipients->pluck('id'))->toContain($recipient->id);
});

it('re-indexes a mail when only its services change', function (): void {
    $mail = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
    $service = Service::factory()->create();

    Queue::fake();

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->fillForm(['primary_services' => [$service->id]])
        ->call('save')
        ->assertHasNoFormErrors();

    // No attribute changed, so the `updated` event never fires: the page has to
    // dispatch the job itself or the new service never reaches the index.
    Queue::assertPushed(IndexIncomingMailJob::class, 1);
    Queue::assertPushed(
        IndexIncomingMailJob::class,
        fn (IndexIncomingMailJob $job): bool => $job->incomingMailId === $mail->id,
    );
    expect($mail->services()->pluck('courrier_services.id'))->toContain($service->id);
});
