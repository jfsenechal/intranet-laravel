<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Enums\ListOuEnum;
use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Ldap\ListAliasLdap;
use AcMarche\EmailManagement\Repository\ListLdapRepository;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Override;

/**
 * The mail groups of OU=LISTS and OU=SERVICES, read live from Active Directory.
 *
 * A Page rather than a Resource: there is no Eloquent model behind these groups, so the
 * table is fed by the custom-data records() function instead of a query builder.
 */
final class ListAliasPage extends Page implements HasTable
{
    use InteractsWithTable;

    #[Override]
    protected string $view = 'email-management::filament.pages.list-alias';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    #[Override]
    protected static ?int $navigationSort = 3;

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
            ]);
    }

    /**
     * Keyed by cn, which is unique within an OU. Filament tracks custom-data rows by these keys
     * across Livewire updates, so a positional key would send an action to the wrong group once
     * the table is searched or filtered.
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
            ->schema([
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
}
