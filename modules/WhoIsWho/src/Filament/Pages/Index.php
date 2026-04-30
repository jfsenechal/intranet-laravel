<?php

declare(strict_types=1);

namespace AcMarche\WhoIsWho\Filament\Pages;

use AcMarche\WhoIsWho\Repository\EmployeeRepository;
use Filament\Pages\Page;
use Filament\Panel;
use Override;

final class Index extends Page
{
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
