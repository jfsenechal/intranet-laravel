<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Emails\Schemas;

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class EmailForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('username')
                    ->default(fn (): ?string => auth()->user()?->username),
                Select::make('sender_id')
                    ->label('Expéditeur')
                    ->relationship('sender', 'name', fn ($query) => $query->where('username', auth()->user()?->username))
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->name} <{$record->email}>")
                    ->required()
                    ->searchable()
                    ->preload()
                    ->columnSpanFull(),
                TextInput::make('subject')
                    ->label('Sujet')
                    ->maxLength(255)
                    ->required()
                    ->columnSpanFull(),
                RichEditor::make('body')
                    ->label('Contenu')
                    ->resizableImages()
                    ->fileAttachmentsMaxSize(8388)
                    ->fileAttachmentsDisk('public')
                    ->fileAttachmentsDirectory(config('mailing-list.uploads.email_attachments'))
                    ->required()
                    ->columnSpanFull(),
                FileUpload::make('attachments')
                    ->label('Pièces jointes')
                    ->multiple()
                    ->maxSize(12588)
                    ->disk('public')
                    ->directory(config('mailing-list.uploads.email_attachments'))
                    ->visibility('public')
                    ->columnSpanFull(),
                Select::make('address_book_ids')
                    ->label('Carnets d\'adresses')
                    ->helperText('Envoyer aux carnets suivants')
                    ->multiple()
                    ->options(fn () => AddressBook::query()
                        ->where('username', auth()->user()?->username)
                        ->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->dehydrated(false),
                Select::make('contact_ids')
                    ->label('Contacts individuels')
                    ->helperText('Envoyer aux contacts suivants')
                    ->multiple()
                    ->options(fn () => Contact::query()
                        ->where('username', auth()->user()?->username)
                        ->get()
                        ->mapWithKeys(fn (Contact $contact): array => [
                            $contact->id => "{$contact->first_name} {$contact->last_name} <{$contact->email}>",
                        ]))
                    ->searchable()
                    ->preload()
                    ->columnSpanFull()
                    ->dehydrated(false),
            ]);
    }
}
