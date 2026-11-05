<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Filament\Pages;

use AcMarche\EmailManagement\Filament\Actions\CreateEmailAction;
use AcMarche\EmailManagement\Filament\Actions\QuotaAction;
use AcMarche\EmailManagement\Filament\Actions\VacationAction;
use AcMarche\EmailManagement\Filament\Schemas\EmployeInfolist;
use AcMarche\EmailManagement\Ldap\EmployeLdap;
use AcMarche\EmailManagement\Repository\EmployeLdapRepository;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Livewire\Attributes\Locked;
use Override;

/**
 * One staff account, read from Active Directory when the page opens.
 *
 * The entry is kept in a locked property: a Livewire update must not depend on the directory
 * still holding it, and the client must not be able to point the actions at another account.
 */
final class ViewEmployePage extends Page
{
    #[Locked]
    public string $samaccountname = '';

    /**
     * @var array{samaccountname: ?string, givenName: ?string, sn: ?string, mail: ?string, telephoneNumber: ?string, dn: ?string}
     */
    #[Locked]
    public array $employe;

    #[Override]
    protected static ?string $slug = 'employes/{samaccountname}';

    #[Override]
    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return ListEmployePage::canAccess();
    }

    public function mount(string $samaccountname): void
    {
        $entry = app(EmployeLdapRepository::class)->getEntry($samaccountname);

        abort_unless($entry instanceof EmployeLdap, 404);

        $this->samaccountname = $samaccountname;
        $this->employe = $entry->toRecord();
    }

    public function getTitle(): string
    {
        return $this->employe['mail'] ?? mb_trim($this->employe['sn'].' '.$this->employe['givenName']);
    }

    /**
     * @return array<int|string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            ListEmployePage::getUrl() => 'Employés',
            $this->getTitle(),
        ];
    }

    #[Override]
    public function content(Schema $schema): Schema
    {
        return EmployeInfolist::configure($schema->state($this->employe), $this->employe);
    }

    /**
     * The page state is what the header and the actions were built from, so reloading is what
     * shows a new address.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return array_map(
            fn (Action $action): Action => $action->authorize(fn (): bool => self::canAccess()),
            [
                QuotaAction::make($this->employe),
                VacationAction::make($this->employe),
                CreateEmailAction::make($this->employe)
                    ->after(fn () => $this->redirect(self::getUrl(['samaccountname' => $this->samaccountname]))),
            ],
        );
    }
}
