<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Enums\ListOuEnum;
use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
use AcMarche\EmailManagement\Repository\ListLdapRepository;
use App\Models\User;
use BackedEnum;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use LdapRecord\LdapRecordException;
use Override;

/**
 * The mail groups of OU=LISTS and OU=SERVICES, read live from Active Directory.
 *
 * A Page rather than a Resource: there is no Eloquent model behind these groups, and no
 * local mirror the way Employe has one, so the table is fed by the custom-data records()
 * function instead of a query builder.
 */
final class ListAliasPage extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected string $view = 'email-management::filament.pages.list-alias';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static ?int $navigationSort = 20;

    #[Override]
    protected static ?string $navigationLabel = 'Listes de diffusion';

    /**
     * The panel already restricts every page to this role through EnsureEmailAdmin. This
     * repeats the check so the page cannot be exposed by a panel that forgets the middleware,
     * and asks the question the same way the middleware does.
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->isAdministrator() || $user->hasRole(RolesEnum::ROLE_EMAIL_ADMIN->value);
    }

    public function getTitle(): string
    {
        return 'Listes de diffusion';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (?string $search, array $filters, int $page, int $recordsPerPage): LengthAwarePaginator => $this->loadRecords($search, $filters, $page, $recordsPerPage))
            ->defaultPaginationPageOption(50)
            ->searchable()
            ->emptyStateHeading('Aucune liste trouvée')
            ->columns([
                TextColumn::make('mail')
                    ->label('Email')
                    ->weight(FontWeight::Medium)
                    ->copyable(),
                TextColumn::make('cn')
                    ->label('Nom (cn)')
                    ->toggleable(),
                TextColumn::make('members_count')
                    ->label('Destinataires')
                    ->badge()
                    ->color('gray')
                    ->state(fn (array $record): int => count($record['members'])),
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(60)
                    ->tooltip(fn (array $record): ?string => $record['description'])
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('ou')
                    ->label('Annuaire')
                    ->options(ListOuEnum::class)
                    ->default(ListOuEnum::LISTS->value)
                    ->selectablePlaceholder(false),
            ])
            ->recordActions([
                $this->viewAction(),
                $this->editDescriptionAction(),
                $this->editMembersAction(),
            ]);
    }

    /**
     * Keyed by cn: it is unique within an OU and is what every action re-reads the entry by.
     * Filament tracks custom-data rows by these keys across Livewire updates, so a positional
     * key would send an action to the wrong group once the table is searched or filtered.
     */
    private function loadRecords(?string $search, array $filters, int $page, int $recordsPerPage): LengthAwarePaginator
    {
        $repository = app(ListLdapRepository::class);
        $ou = $this->resolveOu($filters);

        $entries = filled($search)
            ? $repository->search($ou, $search)
            : $repository->all($ou);

        $records = collect($entries)
            ->mapWithKeys(fn (ListAliasLdap $entry): array => [
                $entry->getFirstAttribute('cn') => [
                    'cn' => $entry->getFirstAttribute('cn'),
                    'mail' => $entry->getFirstAttribute('mail'),
                    'description' => $entry->getFirstAttribute('description'),
                    'dn' => $entry->getDn(),
                    'ou' => $ou->value,
                    'members' => $repository->getMembers($entry),
                ],
            ]);

        return new LengthAwarePaginator(
            $records->forPage($page, $recordsPerPage),
            total: $records->count(),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    private function resolveOu(array $filters): ListOuEnum
    {
        return ListOuEnum::tryFrom($filters['ou']['value'] ?? '') ?? ListOuEnum::LISTS;
    }

    private function viewAction(): Action
    {
        return Action::make('view')
            ->label('Voir')
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->modalHeading(fn (array $record): string => $record['mail'] ?? $record['cn'])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Fermer')
            ->infolist([
                TextEntry::make('cn')
                    ->label('Nom (cn)'),
                TextEntry::make('mail')
                    ->label('Email')
                    ->copyable()
                    ->placeholder('Aucun'),
                TextEntry::make('dn')
                    ->label('DN')
                    ->color('gray'),
                TextEntry::make('description')
                    ->label('Description')
                    ->placeholder('Aucune'),
                TextEntry::make('members')
                    ->label('Destinataires')
                    ->state(fn (array $record): array => $record['members'])
                    ->badge()
                    ->placeholder('Aucun'),
            ]);
    }

    private function editDescriptionAction(): Action
    {
        return Action::make('editDescription')
            ->label('Modifier la description')
            ->icon(Heroicon::PencilSquare)
            ->color('primary')
            ->modalHeading(fn (array $record): string => "Description de {$record['cn']}")
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(fn (array $record): array => ['description' => $record['description']])
            ->schema([
                Textarea::make('description')
                    ->label('Description')
                    ->helperText("Peut être lu par l'utilisateur")
                    ->rows(2)
                    ->nullable()
                    ->maxLength(1024)
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, array $record): void {
                $this->writeToDirectory(
                    $record,
                    fn (ListLdapRepository $repository, ListAliasLdap $entry) => $repository->updateDescription($entry, $data['description'] ?? null),
                    'La liste a bien été modifiée',
                    'Impossible de modifier la liste',
                );
            });
    }

    private function editMembersAction(): Action
    {
        return Action::make('editMembers')
            ->label('Gérer les destinataires')
            ->icon(Heroicon::AtSymbol)
            ->color('info')
            ->modalHeading(fn (array $record): string => "Destinataires de {$record['cn']}")
            ->modalSubmitActionLabel('Enregistrer')
            ->fillForm(fn (array $record): array => ['members' => $record['members']])
            ->schema([
                TagsInput::make('members')
                    ->label('Destinataires')
                    ->placeholder('prenom.nom@marche.be')
                    ->nestedRecursiveRules(['email'])
                    ->helperText('Enregistrer avec la liste vide retire tous les destinataires.')
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, array $record): void {
                $this->writeToDirectory(
                    $record,
                    fn (ListLdapRepository $repository, ListAliasLdap $entry) => $repository->updateMembers($entry, $data['members'] ?? []),
                    'Les destinataires ont bien été modifiés',
                    'Impossible de modifier les destinataires',
                );
            });
    }

    /**
     * The record is a plain array, not a live model, so the entry has to be read back from the
     * directory before it can be written to.
     *
     * @param  array<string, mixed>  $record
     */
    private function writeToDirectory(array $record, callable $write, string $successTitle, string $failureTitle): void
    {
        $repository = app(ListLdapRepository::class);
        $entry = $repository->getEntry(ListOuEnum::from($record['ou']), $record['cn']);

        if (! $entry instanceof ListAliasLdap) {
            Notification::make()
                ->title("Cette liste est introuvable dans l'annuaire")
                ->danger()
                ->send();

            return;
        }

        try {
            $write($repository, $entry);

            Notification::make()
                ->title($successTitle)
                ->success()
                ->send();

            // Filament does not refresh a custom-data table after an action, so without this
            // the row keeps showing the value the directory no longer holds.
            $this->resetTable();
        } catch (Exception|LdapRecordException $e) {
            Notification::make()
                ->title($failureTitle)
                ->body(EmployeLdap::describe($e))
                ->danger()
                ->send();
        }
    }
}
