<?php

namespace AcMarche\Courrier\Filament\Resources\Inbox\Schemas;

use AcMarche\Courrier\Handler\IncomingMailHandler;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

final class InboxInfolist
{
    /**
     * @param  array<string, mixed>|null  $record
     * @return array<int, mixed>
     */
    public static function getEmailViewSchema(?array $record): array
    {
        if (! $record) {
            return [];
        }
        $components = [
            Section::make('Informations')
                ->schema([
                    TextEntry::make('from')
                        ->label('De')
                        ->state($record['from']),
                    TextEntry::make('date')
                        ->label('Date')
                        ->state($record['date']),
                    TextEntry::make('subject')
                        ->label('Objet')
                        ->state($record['subject']),
                ])
                ->columns(3),
        ];

        // Add attachments section if there are any
        if (! empty($record['attachments'])) {
            $attachmentActions = self::buildAttachmentActions($record);
            $components[] = Section::make('Pièces jointes')
                ->icon('tabler-paperclip')
                ->schema([
                    ActionGroup::make($attachmentActions)
                        ->buttonGroup()
                        ->dropdownPlacement('top-start'),
                ]);
        }

        // Add content section
        $content = $record['html'] ?? $record['text'] ?? '';
        $components[] = Section::make('Contenu')
            ->schema([
                TextEntry::make('content')
                    ->hiddenLabel()
                    ->state(new HtmlString($content))
                    ->html(),
            ]);

        return $components;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<int, Action>
     */
    private static function buildAttachmentActions(array $record): array
    {
        $actions = [];
        $attachments = $record['attachments'] ?? [];
        $uid = $record['uid'];
        $attachmentCount = count($attachments);

        foreach ($attachments as $index => $attachment) {
            $filename = $attachment['filename'] ?? 'Sans nom';
            $contentType = $attachment['content_type'] ?? 'application/octet-stream';
            $extension = $attachment['extension'] ?? '';

            $isPreviewable = str_starts_with($contentType, 'image/')
                || $contentType === 'application/pdf';

            $actions[] = Action::make("attachment_{$index}")
                ->label($filename)
                ->icon(self::getAttachmentIcon($contentType))
                ->color('gray')
                ->record(null)
                ->modalHeading("Traiter: {$filename}")
                ->modalWidth(Width::SevenExtraLarge)
                ->fillForm(fn (): array => [
                    'reference_number' => '',
                    'sender' => '',
                    'mail_date' => now(),
                    'description' => $record['subject'] ?? '',
                    'is_registered' => false,
                    'has_acknowledgment' => false,
                ])
                ->schema(fn (): array => InboxForm::getAttachmentFormSchema(
                    $uid,
                    $index,
                    $contentType,
                    $filename,
                    $isPreviewable
                ))
                ->action(function (array $data) use ($uid, $attachmentCount): void {
                    IncomingMailHandler::handleIncomingMailCreation($data, $uid, $attachmentCount);
                })
                ->modalSubmitActionLabel('Enregistrer le courrier');
        }

        return $actions;
    }

    private static function getAttachmentIcon(string $contentType): string
    {
        return match (true) {
            str_starts_with($contentType, 'image/') => 'tabler-photo',
            $contentType === 'application/pdf' => 'tabler-file-type-pdf',
            str_contains($contentType, 'word') => 'tabler-file-type-doc',
            str_contains($contentType, 'excel') || str_contains($contentType, 'spreadsheet') => 'tabler-file-type-xls',
            default => 'tabler-file',
        };
    }
}
