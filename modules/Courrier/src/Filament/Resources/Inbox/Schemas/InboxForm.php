<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Filament\Resources\Inbox\Schemas;

use AcMarche\Courrier\Filament\Resources\IncomingMails\Schemas\IncomingMailForm;

final class InboxForm
{
    public static function getAttachmentFormSchema(
        int $uid,
        int $index,
        string $contentType,
        string $filename
    ): array {

        $previewUrl = route('courrier.attachments.preview', ['uid' => $uid, 'index' => $index]);

        return IncomingMailForm::getComponents([
            'url' => $previewUrl,
            'contentType' => $contentType,
            'filename' => $filename,
        ]);

    }
}
