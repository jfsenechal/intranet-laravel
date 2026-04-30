<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Imports;

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Override;

final class ContactImporter extends Importer
{
    #[Override]
    protected static ?string $model = Contact::class;

    /**
     * @return array<ImportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ImportColumn::make('last_name')
                ->label('Nom')
                ->example('Dupont'),
            ImportColumn::make('first_name')
                ->label('Prénom')
                ->example('Jean'),
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->example('jean.dupont@example.com'),
            ImportColumn::make('phone')
                ->label('Téléphone')
                ->example('+32 123 456 789'),
            ImportColumn::make('description')
                ->label('Description'),
        ];
    }

    /**
     * @return array<\Filament\Forms\Components\Component>
     */
    public static function getOptionsFormComponents(): array
    {
        return [
            Select::make('address_book_id')
                ->label('Carnet d\'adresses')
                ->options(
                    AddressBook::query()
                        ->where('username', auth()->user()?->username)
                        ->pluck('name', 'id')
                )
                ->required()
                ->searchable(),
        ];
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'L\'import de contacts est terminé. '.number_format($import->successful_rows).' '.str('ligne')->plural($import->successful_rows).' importée(s).';

        if (($failedRowsCount = $import->getFailedRowsCount()) !== 0) {
            $body .= ' '.number_format($failedRowsCount).' '.str('ligne')->plural($failedRowsCount).' en échec.';
        }

        return $body;
    }

    public function resolveRecord(): ?Contact
    {
        return Contact::firstOrNew(
            ['email' => $this->data['email']],
        );
    }

    protected function beforeSave(): void
    {
        $this->record->username = auth()->user()?->username;
    }

    protected function afterSave(): void
    {
        $addressBookId = $this->options['address_book_id'] ?? null;

        if ($addressBookId) {
            $this->record->addressBooks()->syncWithoutDetaching([$addressBookId]);
        }
    }
}
