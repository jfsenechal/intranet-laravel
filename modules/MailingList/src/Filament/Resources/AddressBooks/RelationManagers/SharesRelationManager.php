<?php

declare(strict_types=1);

namespace AcMarche\MailingList\Filament\Resources\AddressBooks\RelationManagers;

use AcMarche\MailingList\Enums\SharePermission;
use AcMarche\MailingList\Filament\Resources\AddressBooks\Pages\ViewAddressBook;
use AcMarche\MailingList\Models\AddressBook;
use AcMarche\MailingList\Models\AddressBookShare;
use AcMarche\Security\Repository\UserRepository;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Override;

final class SharesRelationManager extends RelationManager
{
    #[Override]
    protected static string $relationship = 'shares';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return 'Partagé avec ('.$ownerRecord->shares()->count().')';
    }

    /**
     * Shares are listed and managed on the view page only.
     */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $pageClass === ViewAddressBook::class && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    /**
     * Only the owner of the address book manages its shares.
     */
    public function isReadOnly(): bool
    {
        return ! (auth()->user()?->can('manageShares', $this->getOwnerRecord()) ?? false);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('username')
                    ->label('Utilisateur')
                    ->options(fn (): array => $this->shareableUserOptions())
                    ->searchable()
                    ->required()
                    ->hiddenOn('edit'),
                Select::make('permission')
                    ->label('Permission')
                    ->options(SharePermission::class)
                    ->default(SharePermission::Read)
                    ->helperText('Modification : modifier le carnet et ses contacts. Seul le propriétaire peut le supprimer ou gérer les partages.')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('username')
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'))
            ->defaultSort('username')
            ->columns([
                TextColumn::make('user.last_name')
                    ->label('Nom')
                    ->placeholder('-'),
                TextColumn::make('user.first_name')
                    ->label('Prénom')
                    ->placeholder('-'),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->placeholder('-'),
                TextColumn::make('username')
                    ->label('Utilisateur')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('permission')
                    ->label('Permission')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make('share')
                    ->label('Partager')
                    ->icon(Heroicon::Share)
                    ->modalHeading('Partager le carnet')
                    ->successNotificationTitle('Carnet partagé'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Modifier la permission')
                    ->icon(Heroicon::PencilSquare)
                    ->modalHeading(fn (AddressBookShare $record): string => 'Permission de '.$record->username)
                    ->successNotificationTitle('Permission modifiée'),
                DeleteAction::make()
                    ->label('Retirer le partage')
                    ->icon(Heroicon::UserMinus)
                    ->modalHeading('Retirer le partage')
                    ->successNotificationTitle('Partage retiré'),
            ]);
    }

    /**
     * Local users, minus the owner and the users the address book is already shared with.
     *
     * @return array<string, string>
     */
    private function shareableUserOptions(): array
    {
        /** @var AddressBook $addressBook */
        $addressBook = $this->getOwnerRecord();

        $excludedUsernames = $addressBook->shares()
            ->pluck('username')
            ->push($addressBook->username)
            ->all();

        return collect(UserRepository::listLocalUsersForSelectByUsername())
            ->except($excludedUsernames)
            ->all();
    }
}
