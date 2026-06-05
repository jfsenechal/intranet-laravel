<?php

declare(strict_types=1);

use AcMarche\Courrier\Models\Attachment;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Search\AttachmentOcr;
use AcMarche\Courrier\Search\MeiliIndexer;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\ExecutableFinder;

const PDF_WITH_TEXT_LAYER = <<<'PDF'
%PDF-1.4
1 0 obj
<< /Type /Catalog /Pages 2 0 R >>
endobj
2 0 obj
<< /Type /Pages /Kids [3 0 R] /Count 1 >>
endobj
3 0 obj
<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>
endobj
4 0 obj
<< /Length 58 >>
stream
BT /F1 24 Tf 72 700 Td (COURRIER OCR TEST 12345) Tj ET
endstream
endobj
5 0 obj
<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>
endobj
trailer
<< /Root 1 0 R >>
%%EOF
PDF;

/**
 * Build an unsaved attachment model. AttachmentOcr only reads file_name/mime/id,
 * so persistence (and the colliding shared-PDO `attachments` table) is avoided.
 */
function makeAttachment(string $fileName, string $mime, int $id = 1): Attachment
{
    $attachment = new Attachment(['file_name' => $fileName, 'mime' => $mime]);
    $attachment->id = $id;

    return $attachment;
}

function fakeAttachmentDisk(): void
{
    config()->set('courrier.storage.disk', 'ocr-test');
    config()->set('courrier.storage.directory', 'courrier');
    config()->set('courrier.ocr.enabled', true);
    Storage::fake('ocr-test');
}

it('returns an empty string when the attachment file is missing', function (): void {
    fakeAttachmentDisk();

    expect((new AttachmentOcr())->textFor(makeAttachment('missing.pdf', 'application/pdf')))->toBe('');
});

it('returns an empty string when OCR is disabled', function (): void {
    fakeAttachmentDisk();
    config()->set('courrier.ocr.enabled', false);
    Storage::disk('ocr-test')->put('courrier/attachments/doc.pdf', PDF_WITH_TEXT_LAYER);

    expect((new AttachmentOcr())->textFor(makeAttachment('doc.pdf', 'application/pdf')))->toBe('');
});

it('extracts the text layer of a PDF attachment with pdftotext', function (): void {
    if ((new ExecutableFinder())->find('pdftotext') === null) {
        $this->markTestSkipped('pdftotext binary is not available');
    }

    fakeAttachmentDisk();
    Storage::disk('ocr-test')->put('courrier/attachments/doc.pdf', PDF_WITH_TEXT_LAYER);

    expect((new AttachmentOcr())->textFor(makeAttachment('doc.pdf', 'application/pdf')))
        ->toContain('COURRIER OCR TEST 12345');
});

it('caches the extracted text next to the source file', function (): void {
    if ((new ExecutableFinder())->find('pdftotext') === null) {
        $this->markTestSkipped('pdftotext binary is not available');
    }

    fakeAttachmentDisk();
    Storage::disk('ocr-test')->put('courrier/attachments/doc.pdf', PDF_WITH_TEXT_LAYER);

    (new AttachmentOcr())->textFor(makeAttachment('doc.pdf', 'application/pdf', 7));

    Storage::disk('ocr-test')->assertExists('courrier/ocr/7.txt');
});

it('includes attachment OCR text in the search document', function (): void {
    if ((new ExecutableFinder())->find('pdftotext') === null) {
        $this->markTestSkipped('pdftotext binary is not available');
    }

    config()->set('app.meilisearch.master_key', 'test-master-key');
    fakeAttachmentDisk();
    Storage::disk('ocr-test')->put('courrier/attachments/scan.pdf', PDF_WITH_TEXT_LAYER);

    $mail = IncomingMail::factory()->create();
    $mail->load(['recipients', 'services']);
    $mail->setRelation('attachments', collect([makeAttachment('scan.pdf', 'application/pdf')]));

    $document = (new MeiliIndexer())->createDocument($mail);

    expect($document['content'])->toContain('COURRIER OCR TEST 12345');
});
