<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Imports;

use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Component;
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
                ->guess(['Nom de famille'])
                ->example('Dupont'),
            ImportColumn::make('first_name')
                ->label('Prénom')
                ->example('Jean'),
            ImportColumn::make('display_name')
                ->label('Nom à afficher')
                ->helperText('Utilisé pour le nom et le prénom lorsque ceux-ci sont vides.')
                ->fillRecordUsing(fn (): null => null)
                ->example('Jean Dupont'),
            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->rules(['required', 'email', 'max:255'])
                ->guess(['Adresse e-mail principale', 'E-mail', 'Adresse e-mail', 'Courriel'])
                ->example('jean.dupont@example.com'),
            ImportColumn::make('phone')
                ->label('Téléphone')
                ->guess(['Tél. personnel', 'Tél. professionnel'])
                ->example('+32 84 12 34 56'),
            ImportColumn::make('mobile')
                ->label('GSM')
                ->guess(['Portable', 'Mobile', 'Tél. mobile'])
                ->example('+32 470 12 34 56'),
            ImportColumn::make('street')
                ->label('Rue')
                ->guess(['Adresse privée', 'Adresse'])
                ->example('Rue des Écoles 1'),
            ImportColumn::make('postal_code')
                ->label('Code postal')
                ->example('6900'),
            ImportColumn::make('city')
                ->label('Localité')
                ->guess(['Ville'])
                ->example('Marche-en-Famenne'),
            ImportColumn::make('description')
                ->label('Description')
                ->guess(['Notes']),
        ];
    }

    /**
     * @return array<Component>
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
            [
                'email' => $this->data['email'],
                'username' => auth()->user()?->username,
            ],
        );
    }

    /**
     * Splits the display name ("Jean Dupont") into first and last name
     * when the file provides neither, as in Thunderbird exports.
     */
    protected function afterFill(): void
    {
        $displayName = mb_trim((string) ($this->data['display_name'] ?? ''));

        if ($displayName === '' || filled($this->record->first_name) || filled($this->record->last_name)) {
            return;
        }

        $nameParts = preg_split('/\s+/', $displayName, 2);

        $this->record->first_name = count($nameParts) === 2 ? $nameParts[0] : null;
        $this->record->last_name = $nameParts[count($nameParts) - 1];
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
