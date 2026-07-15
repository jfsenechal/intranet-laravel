<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

function createStoredAttachment(string $path, string $fileName = 'doc.pdf'): Attachment
{
    $mail = IncomingMail::factory()->create();

    return Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => $fileName,
        'mime' => 'application/pdf',
        'path' => $path,
    ]);
}

function createStoredAttachmentForDepartment(string $department, string $path): Attachment
{
    $mail = IncomingMail::factory()->create(['department' => $department]);

    return Attachment::create([
        'incoming_mail_id' => $mail->id,
        'file_name' => 'doc.pdf',
        'mime' => 'application/pdf',
        'path' => $path,
    ]);
}

function actingAsUserWithRole(RolesEnum $role): void
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::factory()->create(['name' => $role->value]));
    test()->actingAs($user);
}

it('serves the file at the attachment path column, honouring the legacy folder layout', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/ville/142854/indicateur-ville-6a4e1274d15b43.63314926.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachment($path);

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf')
        ->assertDownload('doc.pdf');
});

it('returns 404 when the stored file is missing', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    Storage::fake(config('courrier.storage.disk'));

    $attachment = createStoredAttachment('courrier/ville/1/missing.pdf');

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertNotFound();
});

it('lets a department reader download an attachment of their department', function (): void {
    actingAsUserWithRole(RolesEnum::ROLE_INDICATEUR_VILLE_READ);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/ville/1/doc.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachmentForDepartment(DepartmentCourrierEnum::VILLE->value, $path);

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertOk()
        ->assertDownload('doc.pdf');
});

it('forbids a department reader from downloading an attachment of another department', function (): void {
    actingAsUserWithRole(RolesEnum::ROLE_INDICATEUR_VILLE_READ);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/cpas/1/doc.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachmentForDepartment(DepartmentCourrierEnum::CPAS->value, $path);

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertForbidden();
});

it('forbids an index-only user from downloading attachments', function (): void {
    actingAsUserWithRole(RolesEnum::ROLE_INDICATEUR_VILLE_INDEX);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/ville/1/doc.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachmentForDepartment(DepartmentCourrierEnum::VILLE->value, $path);

    $this->get(route('courrier.attachments.download', $attachment))
        ->assertForbidden();
});

it('serves the stored preview inline from the path column', function (): void {
    auth()->user()->update(['is_administrator' => true]);

    $disk = Storage::fake(config('courrier.storage.disk'));
    $path = 'courrier/attachments/1_new-upload.pdf';
    $disk->put($path, 'PDF-CONTENT');

    $attachment = createStoredAttachment($path, '1_new-upload.pdf');

    $this->get(route('courrier.attachments.preview-stored', $attachment))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
