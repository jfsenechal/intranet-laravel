<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\Contacts\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

final class ContactForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                ...self::columns(),
                Hidden::make('username')
                    ->default(fn (): ?string => auth()->user()?->username),
                CheckboxList::make('addressBooks')
                    ->label('Carnets d\'adresses')
                    ->relationship('addressBooks', 'name'),
                // ->searchable(),
            ]);
    }

    public static function columns(): array
    {
        return [
            TextInput::make('last_name')
                ->label('Nom')
                ->maxLength(255),
            TextInput::make('first_name')
                ->label('Prénom')
                ->maxLength(255),
            TextInput::make('email')
                ->email()
                ->unique(ignoreRecord: true)
                ->maxLength(255)
                ->required(),
            TextInput::make('phone')
                ->label('Téléphone')
                ->tel()
                ->maxLength(255),
            Textarea::make('description')
                ->rows(3),
        ];
    }
}
