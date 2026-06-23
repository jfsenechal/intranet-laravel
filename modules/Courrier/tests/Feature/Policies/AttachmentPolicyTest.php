<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use AcMarche\Security\Models\Role;

function createCourrierAttachment(?string $department = null): Attachment
{
    $mail = IncomingMail::factory()->create(['department' => $department]);

    return Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
    ]);
}

it('allows a global administrator to download any attachment', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    expect(auth()->user()->can('download', createCourrierAttachment(DepartmentCourrierEnum::VILLE->value)))->toBeTrue();
});

it('allows a recipient to download the attachment', function (): void {
    $user = auth()->user();
    $attachment = createCourrierAttachment();
    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $attachment->incomingMail->recipients()->attach($recipient->id);

    expect($user->can('download', $attachment))->toBeTrue();
});

it('allows a linked-service member to download the attachment', function (): void {
    $user = auth()->user();
    $attachment = createCourrierAttachment();
    $service = Service::factory()->create();
    $recipient = Recipient::factory()->create(['username' => $user->username]);
    $recipient->services()->attach($service->id);
    $attachment->incomingMail->services()->attach($service->id);

    expect($user->can('download', $attachment))->toBeTrue();
});

it('allows a department administrator to download an attachment of their department', function (): void {
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('download', createCourrierAttachment(DepartmentCourrierEnum::VILLE->value)))->toBeTrue();
});

it('denies a department administrator to download an attachment of another department', function (): void {
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('download', createCourrierAttachment(DepartmentCourrierEnum::CPAS->value)))->toBeFalse();
});

it('denies an index user to download an attachment of their department', function (): void {
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value]);
    auth()->user()->roles()->attach($role);

    expect(auth()->user()->can('download', createCourrierAttachment(DepartmentCourrierEnum::VILLE->value)))->toBeFalse();
});

it('denies a user with no link to the mail to download the attachment', function (): void {
    expect(auth()->user()->can('download', createCourrierAttachment(DepartmentCourrierEnum::VILLE->value)))->toBeFalse();
});
