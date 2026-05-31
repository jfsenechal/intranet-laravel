<?php

declare(strict_types=1);

namespace AcMarche\Agent\Filament\Pages;

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Employee;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Override;
use UnitEnum;

final class IntegrityCheck extends Page
{
    #[Override]
    protected string $view = 'agent::filament.pages.integrity-check';

    #[Override]
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldExclamation;

    #[Override]
    protected static ?int $navigationSort = 50;

    #[Override]
    protected static ?string $navigationLabel = 'Intégrité des profils';

    #[Override]
    protected static string|UnitEnum|null $navigationGroup = 'Paramètres';

    public function getTitle(): string
    {
        return 'Vérification de l\'intégrité des profils';
    }

    /**
     * @return array<int, array{profile: Profile, issue: string, employee: ?Employee}>
     */
    public function getIssues(): array
    {
        $issues = [];

        $profiles = Profile::query()
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $employeeIds = $profiles
            ->pluck('employee_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        $employees = Employee::query()
            ->whereIn('id', $employeeIds)
            ->get()
            ->keyBy('id');

        foreach ($profiles as $profile) {
            if (empty($profile->employee_id)) {
                $issues[] = [
                    'profile' => $profile,
                    'issue' => 'Aucun employee_id défini sur le profil.',
                    'employee' => null,
                ];

                continue;
            }

            $employee = $employees->get($profile->employee_id);

            if (! $employee instanceof Employee) {
                $issues[] = [
                    'profile' => $profile,
                    'issue' => sprintf(
                        'Aucun employé trouvé dans la table GRH pour employee_id = %d.',
                        $profile->employee_id,
                    ),
                    'employee' => null,
                ];

                continue;
            }

            $mismatches = [];

            if ($this->normalize($profile->last_name) !== $this->normalize($employee->last_name)) {
                $mismatches[] = sprintf(
                    'nom : « %s » vs « %s »',
                    (string) $profile->last_name,
                    (string) $employee->last_name,
                );
            }

            if ($this->normalize($profile->first_name) !== $this->normalize($employee->first_name)) {
                $mismatches[] = sprintf(
                    'prénom : « %s » vs « %s »',
                    (string) $profile->first_name,
                    (string) $employee->first_name,
                );
            }

            if ($mismatches !== []) {
                $issues[] = [
                    'profile' => $profile,
                    'issue' => 'Différences profil / employé — '.implode(' ; ', $mismatches),
                    'employee' => $employee,
                ];
            }
        }

        return $issues;
    }

    private function normalize(?string $value): string
    {
        return mb_strtolower(mb_trim((string) $value));
    }
}
