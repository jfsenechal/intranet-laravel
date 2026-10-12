<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\RelationManagers;

use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Filament\Resources\Contacts\ContactResource;
use AcMarche\MailingList\Filament\Resources\Contacts\Schemas\ContactForm;
use AcMarche\MailingList\Models\Contact;
use AcMarche\MailingList\Repositories\OwnerScope;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class ContactsRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'contacts';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Contacts ('.$ownerRecord->contacts()->count().')';
    }

    /**
     * Contacts are managed from the view page only, not from the edit form.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewAddressBook::class && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    /**
     * The panel makes relation managers read-only on resource view pages, which
     * denies every EditAction; the contacts stay editable for users who may
     * edit the address book (its owner and write shares), read-only otherwise.
     */
    public function isReadOnly(): bool
    {
        return ! $this->canManageAddressBook();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components(ContactForm::columns());
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('email')
            ->defaultSort('last_name')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScope(OwnerScope::class))
            ->recordUrl(fn (Contact $record): ?string => $this->ownsContact($record)
                ? ContactResource::getUrl('view', ['record' => $record])
                : null)
            ->columns([
                TextColumn::make('last_name')
                    ->label('Nom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('first_name')
                    ->label('Prénom')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('email')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('phone')
                    ->label('Téléphone')
                    ->searchable()
                    ->toggleable(),
            ])
            ->headerActions([
                CreateAction::make('addContact')
                    ->label('Ajouter un contact')
                    ->icon(Heroicon::UserPlus)
                    ->color('success')
                    ->modalHeading('Ajouter un contact au carnet')
                    ->mutateDataUsing(fn (array $data): array => [
                        ...$data,
                        'username' => auth()->user()?->username,
                    ])
                    ->successNotificationTitle('Contact ajouté'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier')
                    ->icon(Heroicon::PencilSquare)
                    ->url(fn (Contact $record): ?string => $this->ownsContact($record)
                        ? ContactResource::getUrl('edit', ['record' => $record])
                        : null),
                DetachAction::make()
                    ->label('Retirer du carnet')
                    ->icon(Heroicon::UserMinus)
                    ->modalHeading('Retirer le contact du carnet')
                    ->successNotificationTitle('Contact retiré du carnet'),
            ]);
    }

    private function canManageAddressBook(): bool
    {
        return auth()->user()?->can('update', $this->getOwnerRecord()) ?? false;
    }

    /**
     * Every contact of the address book is listed, but the contact pages only
     * open the user's own contacts; others are edited in a modal.
     */
    private function ownsContact(Contact $contact): bool
    {
        return $contact->username === auth()->user()?->username;
    }
}
