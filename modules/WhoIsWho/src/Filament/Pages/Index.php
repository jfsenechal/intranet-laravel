<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Pages;

use AcMarche\Security\Enums\RolesEnum;
use AcMarche\WhoIsWho\Filament\Concerns\InteractsWithFavoriteEmployees;
use AcMarche\WhoIsWho\Filament\Exports\EmployeeDirectoryExport;
use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Override;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class Index extends Page
{
    use InteractsWithFavoriteEmployees;

    public ?string $letter = null;

    #[Override]
    protected string $view = 'who-is-who::filament.pages.index';

    #[Override]
    protected static ?int $navigationSort = 1;

    public static function getRoutePath(Panel $panel): string
    {
        return '/';
    }

    public static function getNavigationLabel(): string
    {
        return 'Annuaire A → Z';
    }

    public static function getNavigationIcon(): string
    {
        return 'heroicon-o-list-bullet';
    }

    /**
     * The directory itself is open to every authenticated user, but the full
     * staff listing as a file is not: only intranet administrators may pull it
     * out of the application. Hiding the action also refuses it when mounted,
     * so this is the only gate the export needs.
     */
    public static function canExport(): bool
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->isAdministrator() || $user->hasRole(RolesEnum::INTRANET_ADMIN->value);
    }

    public function getTitle(): string
    {
        return 'Qui est qui ? Annuaire A → Z';
    }

    public function mount(): void
    {
        $this->letter = request()->query('letter') !== null
            ? mb_strtoupper((string) request()->query('letter'))
            : null;
    }

    public function selectLetter(?string $letter): void
    {
        $this->letter = $letter !== null ? mb_strtoupper($letter) : null;
    }

    /**
     * @return array<Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('export')
                ->label('Exporter en XLSX')
                ->icon(Heroicon::ArrowDownTray)
                ->color('warning')
                ->visible(fn (): bool => self::canExport())
                ->action(fn (): StreamedResponse => new EmployeeDirectoryExport(
                    EmployeeRepository::activeAgentsQuery(),
                )->downloadXlsx('annuaire.xlsx')),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        $grouped = EmployeeRepository::groupedByLetter();
        $letters = $grouped->keys()->all();

        $employees = $this->letter !== null && $grouped->has($this->letter)
            ? $grouped->get($this->letter)
            : $grouped->flatten(1);

        return [
            'letters' => $letters,
            'currentLetter' => $this->letter,
            'employees' => $employees,
            'grouped' => $grouped,
        ];
    }
}
