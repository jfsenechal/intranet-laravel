<?php

namespace AcMarche\Courrier\Filament\Resources\Inbox\Schemas;

use AcMarche\Courrier\Models\Recipient;
use AcMarche\Courrier\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;

final class InboxForm
{
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
}
