<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use Illuminate\Support\Facades\DB;

function insertAttachment(int $incomingMailId, string $fileName): int
{
    return DB::connection('maria-courrier')->table('attachments')->insertGetId([
        'incoming_mail_id' => $incomingMailId,
        'file_name' => $fileName,
        'mime' => 'application/pdf',
        'updated_at' => now(),
    ]);
}

function attachmentPath(int $id): ?string
{
    return DB::connection('maria-courrier')->table('attachments')->where('id', $id)->value('path');
}

it('builds the legacy relative path', function (): void {
    expect(Attachment::legacyPath('Cpas', 123, 'file.pdf'))
        ->toBe('data/indicateur/cpas/123/file.pdf');
});

it('backfills VILLE attachments using the in-place mail id', function (): void {
    $mail = IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::VILLE->value,
        'mail_date' => today(),
    ]);
    $attachmentId = insertAttachment($mail->id, 'rapport.pdf');

    Attachment::backfillLegacyPaths();

    expect(attachmentPath($attachmentId))
        ->toBe('data/indicateur/ville/'.$mail->id.'/rapport.pdf');
});

it('backfills migrated CPAS attachments using the legacy old_id', function (): void {
    $mail = IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::CPAS->value,
        'mail_date' => today(),
    ]);
    DB::connection('maria-courrier')->table('incoming_mails')
        ->where('id', $mail->id)
        ->update(['old_id' => 98765]);
    $attachmentId = insertAttachment($mail->id, 'scan.pdf');

    Attachment::backfillLegacyPaths();

    expect(attachmentPath($attachmentId))
        ->toBe('data/indicateur/cpas/98765/scan.pdf');
});

it('leaves the path null when the mail has no department', function (): void {
    $mail = IncomingMail::factory()->create([
        'department' => null,
        'mail_date' => today(),
    ]);
    $attachmentId = insertAttachment($mail->id, 'orphan.pdf');

    Attachment::backfillLegacyPaths();

    expect(attachmentPath($attachmentId))->toBeNull();
});
