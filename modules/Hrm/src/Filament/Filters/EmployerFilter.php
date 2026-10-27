<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Filters;

use AcMarche\Hrm\Models\Employer;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

final class EmployerFilter
{
    /**
     * Suffix of the option value that matches an employer together with its children.
     * A bare id matches that employer only.
     */
    private const string WITH_CHILDREN_SUFFIX = '-children';

    public static function make(): SelectFilter
    {
        return SelectFilter::make('employer_id')
            ->label('Employeur')
            ->options(fn (): array => self::options())
            ->searchable()
            ->preload()
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, int|string $value): Builder => $query->whereIn(
                    'employer_id',
                    self::employerIds($value),
                ),
            ));

    }

    /**
     * @param  bool  $activeOnly  Match only through contracts that are still
     *                            running. Leave it off for listings of past
     *                            records (absences, trainings), where the
     *                            employer of the time is what matters.
     */
    public static function makeThrough(string $relation, bool $activeOnly = false): SelectFilter
    {
        return SelectFilter::make('employer_id')
            ->label('Employeur')
            ->options(fn (): array => self::options())
            ->searchable()
            ->preload()
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['value'] ?? null,
                fn (Builder $query, int|string $value): Builder => $query->whereHas(
                    $relation,
                    function (Builder $query) use ($value, $activeOnly): void {
                        $query->whereIn('employer_id', self::employerIds($value));

                        if ($activeOnly) {
                            $query->active();
                        }
                    },
                ),
            ));
    }

    /**
     * Same grouping as Employer::groupedSelectOptions(), plus an extra
     * "with children" entry for every parent that has children.
     *
     * @return array<string, array<int|string, string>>
     */
    private static function options(): array
    {
        $options = [];
        foreach (Employer::groupedSelectOptions() as $groupName => $group) {
            $parentId = array_key_first($group);
            if (count($group) > 1) {
                $group = [$parentId.self::WITH_CHILDREN_SUFFIX => $group[$parentId].' (avec enfants)'] + $group;
            }
            $options[$groupName] = $group;
        }

        return $options;
    }

    /**
     * @return array<int, int>
     */
    private static function employerIds(int|string $value): array
    {
        if (str_ends_with((string) $value, self::WITH_CHILDREN_SUFFIX)) {
            return Employer::descendantsAndSelfIds((int) $value);
        }

        return [(int) $value];
    }
}
