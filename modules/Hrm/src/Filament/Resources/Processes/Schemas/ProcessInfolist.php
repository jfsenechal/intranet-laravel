<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Processes\Schemas;

use AcMarche\Hrm\Models\Process;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

final class ProcessInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('Procédure')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('description')
                            ->label('Description')
                            ->hiddenLabel()
                            ->formatStateUsing(fn (?string $state): ?string => self::plainTextToHtml($state))
                            ->html()
                            ->prose()
                            ->placeholder('—'),
                    ]),
                Section::make('Métadonnées')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Créé le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                        TextEntry::make('updated_at')
                            ->label('Modifié le')
                            ->dateTime('d/m/Y H:i')
                            ->placeholder('—'),
                    ]),
                Section::make('Pièces jointes')
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('attachments')
                            ->label('Pièces jointes')
                            ->hiddenLabel()
                            ->formatStateUsing(fn (string $state, Process $record): string => self::attachmentLink($state, $record))
                            ->html()
                            ->listWithLineBreaks()
                            ->placeholder('—'),
                    ]),
            ]);
    }

    /**
     * Descriptions saved before the rich editor are plain text: keep their line breaks.
     */
    public static function plainTextToHtml(?string $state): ?string
    {
        if ($state === null || $state === '' || $state !== strip_tags($state)) {
            return $state;
        }

        return nl2br(e($state));
    }

    /**
     * Temporary download link to an attachment, labelled with the name it was uploaded with.
     */
    public static function attachmentLink(string $path, Process $record): string
    {
        return sprintf(
            '<a href="%s" target="_blank" class="text-primary-600 hover:underline">%s</a>',
            e(Storage::disk('local')->temporaryUrl($path, now()->addMinutes(5))),
            e($record->attachment_names[$path] ?? basename($path)),
        );
    }
}
