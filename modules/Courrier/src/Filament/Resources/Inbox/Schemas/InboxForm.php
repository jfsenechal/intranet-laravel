<?php

namespace AcMarche\Courrier\Filament\Resources\Inbox\Schemas;

use AcMarche\Courrier\Handler\IncomingMailHandler;
use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

final class InboxForm
{
    /**
     * @param  array<string, mixed>  $record
     * @return array<int, mixed>
     */
    public static function getEmailViewSchema(array $record): array
    {
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

    public static function getAttachmentFormSchema(
        string $uid,
        int $index,
        string $contentType,
        string $filename,
        bool $isPreviewable
    ): array {

        $previewUrl = route('courrier.attachments.preview', ['uid' => $uid, 'index' => $index]);

        $components = [];

        // Add preview section for images and PDFs
        if ($isPreviewable) {
            $components[] = Section::make('Aperçu')
                ->schema([
                    View::make('courrier::components.attachment-preview')
                        ->viewData([
                            'url' => $previewUrl,
                            'contentType' => $contentType,
                            'filename' => $filename,
                        ]),
                ])
                ->collapsible();
        }

        // Add the form
        $components[] = Flex::make([
            Section::make('Informations du courrier')
                ->schema([
                    TextInput::make('reference_number')
                        ->label('Numéro')
                        ->required()
                        ->maxLength(255),
                    DatePicker::make('mail_date')
                        ->label('Date du courrier')
                        ->required()
                        ->default(now())
                        ->native(false),
                    TextInput::make('sender')
                        ->label('Expéditeur')
                        ->required()
                        ->maxLength(255),
                    RichEditor::make('description')
                        ->label('Description')
                        ->columnSpanFull(),
                ])
                ->columns(2)
                ->columnSpan(2),
            Section::make('Options')
                ->schema([
                    Toggle::make('is_registered')
                        ->label('Recommandé')
                        ->default(false),
                    Toggle::make('has_acknowledgment')
                        ->label('Accusé de réception')
                        ->default(false),
                ])
                ->grow(false),
        ])->from('md');

        // Add services and recipients
        $components[] = Section::make('Affectation')
            ->schema([
                Select::make('primary_services')
                    ->label('Services principaux')
                    ->options(Service::query()->where('is_active', true)->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('secondary_services')
                    ->label('Services secondaires')
                    ->options(Service::query()->where('is_active', true)->pluck('name', 'id'))
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('primary_recipients')
                    ->label('Destinataires principaux')
                    ->options(
                        Recipient::query()
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn (Recipient $r) => [$r->id => "{$r->first_name} {$r->last_name}"])
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Select::make('secondary_recipients')
                    ->label('Destinataires secondaires')
                    ->options(
                        Recipient::query()
                            ->where('is_active', true)
                            ->get()
                            ->mapWithKeys(fn (Recipient $r) => [$r->id => "{$r->first_name} {$r->last_name}"])
                    )
                    ->multiple()
                    ->searchable()
                    ->preload(),
            ])
            ->columns(2);

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
                ->modalHeading("Traiter: {$filename}")
                ->modalWidth(Width::SevenExtraLarge)
                ->fillForm(fn (): array => [
                    'reference_number' => '',
                    'sender' => $record['from_name'] ?: $record['from_email'],
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
