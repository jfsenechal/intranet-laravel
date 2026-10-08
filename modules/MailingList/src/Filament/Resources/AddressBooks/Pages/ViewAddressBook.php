<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\Pages;

use AcMarche\MailingList\Filament\Resources\AddressBooks\AddressBookResource;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Schemas\AddressBookInfolist;
use AcMarche\MailingList\Filament\Resources\Contacts\Schemas\ContactForm;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\Contact;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Override;

final class ViewAddressBook extends ViewRecord
{
    #[Override]
    protected static string $resource = AddressBookResource::class;

    public function getTitle(): string
    {
        return $this->record->name;
    }

    public function infolist(Schema $schema): Schema
    {
        return AddressBookInfolist::configure($schema, $this->record->id);
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make('addContact')
                ->label('Ajouter un contact')
                ->icon(Heroicon::UserPlus)
                ->color('success')
                ->modal()
                ->modalHeading('Ajouter un contact au carnet')
                ->schema(fn (Schema $schema): Schema => $schema->components(ContactForm::columns())->model(Contact::class))
                ->action(function (array $data, AddressBook $record): void {
                    $record->contacts()->create([
                        ...$data,
                        'username' => auth()->user()?->username,
                    ]);
                })
                ->successNotificationTitle('Contact ajouté'),
            EditAction::make()
                ->label('Modifier')
                ->icon(Heroicon::PencilSquare),
            DeleteAction::make()
                ->label('Supprimer')
                ->icon(Heroicon::Trash),
        ];
    }
}
