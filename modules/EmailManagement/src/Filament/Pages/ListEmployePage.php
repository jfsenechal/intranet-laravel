<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Enums\RolesEnum;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;

/**
 * The staff accounts of Active Directory, read live.
 *
 * A Page rather than a Resource: nothing is stored locally, so there is no Eloquent model
 * behind the table and it is fed by the custom-data records() function.
 */
final class ListEmployePage extends Page implements HasTable
{
    use InteractsWithTable;

    /**
     * The fields the table search looks through.
     */
    private const array SEARCHED_FIELDS = ['sn', 'givenName', 'mail', 'samaccountname', 'telephoneNumber'];

    #[Override]
    protected string $view = 'email-management::filament.pages.list-employes';

    #[Override]
    protected static ?string $slug = 'employes';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    #[Override]
    protected static ?int $navigationSort = 1;

    #[Override]
    protected static ?string $navigationLabel = 'Employés';

    /**
     * Filament checks no policy on array records, so this is the only gate of the two employe
     * pages besides the panel's EnsureEmailAdmin middleware.
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
        return 'Employés';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int $recordsPerPage): LengthAwarePaginator => $this->loadRecords($search, $sortColumn, $sortDirection, $page, $recordsPerPage))
            ->defaultPaginationPageOption(50)
            ->searchable()
            ->emptyStateHeading('Aucun employé trouvé')
            ->recordUrl(fn (array $record): string => ViewEmployePage::getUrl(['samaccountname' => $record['samaccountname']]))
            ->columns([
                TextColumn::make('sn')
                    ->label('Nom')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('givenName')
                    ->label('Prénom')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('mail')
                    ->label('Email')
                    ->sortable(),
                TextColumn::make('samaccountname')
                    ->label('Identifiant')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('telephoneNumber')
                    ->label('Téléphone')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Voir')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->url(fn (array $record): string => ViewEmployePage::getUrl(['samaccountname' => $record['samaccountname']])),
            ]);
    }

    /**
     * Keyed by samaccountname, which is what the view page reads the entry by. An entry without
     * one cannot be addressed, so it is left out.
     *
     * The directory is read whole and searched here rather than through an LDAP filter: the
     * table covers a few hundred accounts, and a search term never reaches a directory query.
     */
    private function loadRecords(?string $search, ?string $sortColumn, ?string $sortDirection, int $page, int $recordsPerPage): LengthAwarePaginator
    {
        $sortColumn ??= 'sn';

        $records = collect(app(EmployeLdapRepository::class)->all())
            ->map(fn (EmployeLdap $entry): array => $entry->toRecord())
            ->filter(fn (array $record): bool => filled($record['samaccountname']))
            ->keyBy('samaccountname')
            ->when(filled($search), fn (Collection $records): Collection => $records->filter(
                fn (array $record): bool => $this->matchesSearch($record, (string) $search)
            ))
            ->sortBy(
                fn (array $record): string => Str::lower(Str::ascii((string) ($record[$sortColumn] ?? ''))),
                descending: $sortDirection === 'desc',
            );

        return new LengthAwarePaginator(
            $records->forPage($page, $recordsPerPage),
            total: $records->count(),
            perPage: $recordsPerPage,
            currentPage: $page,
        );
    }

    /**
     * @param  array<string, ?string>  $record
     */
    private function matchesSearch(array $record, string $search): bool
    {
        foreach (self::SEARCHED_FIELDS as $field) {
            if (Str::contains((string) $record[$field], $search, ignoreCase: true)) {
                return true;
            }
        }

        return false;
    }
}
