<?php

namespace AcMarche\Courrier\Filament\Resources\Inbox\Tables;

use AcMarche\Courrier\Repository\ImapRepository;
use Filament\Actions\Action;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

final class InboxTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->records(fn (): array => ImapRepository::getMessages())
            ->columns([
                IconColumn::make('has_attachments')
                    ->label('')
                    ->width('40px')
                    ->icon(fn (array $record): ?string => $record['has_attachments'] ? 'tabler-paperclip' : null)
                    ->color('gray'),
                TextColumn::make('date')
                    ->label('Date')
                    ->width('150px')
                    ->sortable(),
                TextColumn::make('from')
                    ->label('Expéditeur')
                    ->width('250px')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Objet')
                    ->searchable()
                    ->wrap(),
            ])
            ->defaultSort('date', 'desc')
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->color('gray')
                    ->icon(Heroicon::Eye)
                    ->modalHeading(fn (array $record): string => $record['subject'] ?? 'Sans objet')
                    ->modalWidth(Width::FiveExtraLarge)
                    ->schema(fn (array $record): array => self::getEmailViewSchema($record))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Fermer'),
            ])
            ->paginated([10, 25, 50]);
    }

    /**
     * @param  array<string, mixed>  $record
     * @return array<int, mixed>
     */
    private static function getEmailViewSchema(array $record): array
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
            $components[] = Section::make('Pièces jointes')
                ->icon('tabler-paperclip')
                ->schema([
                    RepeatableEntry::make('attachments')
                        ->hiddenLabel()
                        ->state($record['attachments'])
                        ->schema([
                            TextEntry::make('filename')
                                ->label('Fichier')
                                ->icon('tabler-file'),
                            TextEntry::make('content_type')
                                ->label('Type'),
                        ])
                        ->columns(2),
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
}
