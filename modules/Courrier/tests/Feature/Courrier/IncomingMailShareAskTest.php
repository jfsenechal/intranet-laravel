<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\ViewIncomingMail;
use AcMarche\Courrier\Mail\AskAttachment;
use AcMarche\Courrier\Mail\ShareIncomingMail;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Courrier\Repository\RecipientRepository;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

function mailWithAttachment(string $department = DepartmentCourrierEnum::VILLE->value): IncomingMail
{
    $mail = IncomingMail::factory()->create(['department' => $department]);

    Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => 'courrier/ville/1/doc.pdf',
    ]);

    return $mail;
}

it('lets a user who can download share the courrier with recipients', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => true]));
    Mail::fake();

    $mail = mailWithAttachment();
    $recipient = Recipient::factory()->create();

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertActionVisible('share')
        ->assertActionHidden('ask')
        ->mountAction('share')
        ->assertMountedActionModalSee('Le courrier partira en pièce jointe.')
        ->fillForm(['recipients' => [$recipient->id], 'note' => 'Voici le courrier'])
        ->callMountedAction()
        ->assertHasNoActionErrors()
        ->assertNotified();

    Mail::assertSent(
        ShareIncomingMail::class,
        fn (ShareIncomingMail $sent): bool => $sent->hasTo($recipient->email)
            && $sent->incomingMail->is($mail)
            && $sent->note === 'Voici le courrier',
    );
});

it('requires readers to ask for the attachment, but not a note', function (): void {
    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]));
    $this->actingAs($user);
    Mail::fake();

    $mail = mailWithAttachment(DepartmentCourrierEnum::VILLE->value);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->callAction('ask', data: ['readers' => [], 'note' => null])
        ->assertHasActionErrors(['readers' => 'required']);

    Mail::assertNothingSent();

    // A note stays optional: picking a reader alone is enough to send the ask.
    $service = Service::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
    $reader = Recipient::factory()->receivesAttachments()->create();
    $reader->services()->attach($service);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->callAction('ask', data: ['readers' => [$reader->id], 'note' => null])
        ->assertHasNoActionErrors()
        ->assertNotified();

    Mail::assertSent(
        AskAttachment::class,
        fn (AskAttachment $sent): bool => $sent->hasTo($reader->email) && $sent->note === null,
    );
});

it('searches share recipients server side instead of listing them all', function (): void {
    $match = Recipient::factory()->create(['last_name' => 'Dupont', 'first_name' => 'Jean']);
    $other = Recipient::factory()->create(['last_name' => 'Martin', 'first_name' => 'Paul']);

    $results = RecipientRepository::searchShareOptions('dupo');

    expect($results->keys()->all())->toBe([$match->id])
        ->and($results->get($match->id))->toBe('Dupont Jean')
        ->and($results->has($other->id))->toBeFalse();

    expect(RecipientRepository::getShareLabels([$other->id])->all())
        ->toBe([$other->id => 'Martin Paul']);
});

it('hides the share action from a user who cannot download', function (): void {
    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]));
    $this->actingAs($user);

    $mail = mailWithAttachment(DepartmentCourrierEnum::VILLE->value);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->assertActionHidden('share')
        ->assertActionVisible('ask');
});

it('lets a user who cannot download ask the department attachment readers', function (): void {
    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]));
    $this->actingAs($user);
    Mail::fake();

    $mail = mailWithAttachment(DepartmentCourrierEnum::VILLE->value);

    $service = Service::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);
    $reader = Recipient::factory()->receivesAttachments()->create();
    $reader->services()->attach($service);

    // Excluded: right department but does not receive attachments.
    $noAttachments = Recipient::factory()->create(['receives_attachments' => false]);
    $noAttachments->services()->attach($service);

    // Excluded: receives attachments but in another department.
    $otherService = Service::factory()->create(['department' => DepartmentCourrierEnum::CPAS->value]);
    $otherDepartment = Recipient::factory()->receivesAttachments()->create();
    $otherDepartment->services()->attach($otherService);

    // Only the eligible readers of the department are offered as options.
    expect(RecipientRepository::getAttachmentReaderOptions(DepartmentCourrierEnum::VILLE->value)->keys()->all())
        ->toBe([$reader->id]);

    livewire(ViewIncomingMail::class, ['record' => $mail->id])
        ->callAction('ask', data: [
            'readers' => [$reader->id],
            'note' => 'Merci de me la transmettre',
        ])
        ->assertHasNoActionErrors()
        ->assertNotified();

    Mail::assertSent(
        AskAttachment::class,
        fn (AskAttachment $sent): bool => $sent->hasTo($reader->email)
            && $sent->askerEmail === $user->email
            && $sent->note === 'Merci de me la transmettre',
    );
    Mail::assertNotSent(
        AskAttachment::class,
        fn (AskAttachment $sent): bool => $sent->hasTo($noAttachments->email) || $sent->hasTo($otherDepartment->email),
    );
});
