<?php

declare(strict_types=1);

namespace AcMarche\Mileage\Filament\Pages;

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Repository\RateMismatchRepository;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Override;
use UnitEnum;

final class RateMismatchReport extends Page
{
    public string $search = '';

    public string $sort = 'delta';

    public int $fromYear = 2024;

    #[Override]
    protected string $view = 'mileage::filament.pages.rate-mismatch-report';

    #[Override]
    protected static ?int $navigationSort = 5;

    #[Override]
    protected static ?string $navigationLabel = 'Tarifs hors barème';

    #[Override]
    protected static string|null|UnitEnum $navigationGroup = 'Administration';

    public static function getNavigationIcon(): string
    {
        return 'tabler-alert-triangle';
    }

    public static function canAccess(array $parameters = []): bool
    {
        $user = Auth::user();
        if ($user?->isAdministrator()) {
            return true;
        }

        return $user?->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value) ?? false;
    }

    public function getTitle(): string
    {
        return 'Déclarations avec un tarif hors barème';
    }

    /**
     * @return array<int, string>
     */
    public function getYearOptions(): array
    {
        return [
            '2026' => 'Depuis 2026',
            '2025' => 'Depuis 2025',
            '2024' => 'Depuis 2024',
         //   '2023' => 'Depuis 2023',
         //   '2022' => 'Depuis 2022',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        $declarations = RateMismatchRepository::findSince($this->fromYear);

        $totalDeclarations = count($declarations);
        $totalTrips = array_sum(array_column($declarations, 'trip_count'));
        $totalDelta = round(array_sum(array_column($declarations, 'delta')), 2);

        return [
            'declarations' => $this->sortDeclarations($this->filterDeclarations($declarations)),
            'totalDeclarations' => $totalDeclarations,
            'totalTrips' => $totalTrips,
            'totalDelta' => $totalDelta,
            'storageInconsistencies' => RateMismatchRepository::countStorageInconsistenciesSince($this->fromYear),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $declarations
     * @return array<int, array<string, mixed>>
     */
    private function filterDeclarations(array $declarations): array
    {
        $needle = mb_strtolower(mb_trim($this->search));

        if ($needle === '') {
            return $declarations;
        }

        return array_values(array_filter(
            $declarations,
            function (array $declaration) use ($needle): bool {
                $haystack = mb_strtolower(implode(' ', [
                    $declaration['id'],
                    $declaration['last_name'],
                    $declaration['first_name'],
                    $declaration['user_add'] ?? '',
                ]));

                return str_contains($haystack, $needle);
            }
        ));
    }

    /**
     * @param  array<int, array<string, mixed>>  $declarations
     * @return array<int, array<string, mixed>>
     */
    private function sortDeclarations(array $declarations): array
    {
        usort($declarations, fn (array $a, array $b): int => match ($this->sort) {
            'trips' => $b['trip_count'] <=> $a['trip_count'] ?: $b['id'] <=> $a['id'],
            'name' => [$a['last_name'], $a['first_name']] <=> [$b['last_name'], $b['first_name']],
            'id' => $b['id'] <=> $a['id'],
            default => abs($b['delta']) <=> abs($a['delta']),
        });

        return $declarations;
    }
}
