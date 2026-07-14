<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\Absences\Schemas;

use AcMarche\Hrm\Enums\RolesEnum;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Services\AbsenceNotifier;
use App\Models\User;
use Filament\Schemas\Components\Callout;
use Filament\Support\Icons\Heroicon;

final class AbsenceCallouts
{
    /**
     * @return array<int, Callout>
     */
    public static function components(): array
    {
        return [
            self::proximityCallout(),
            self::cesiCallout(),
            self::workPotentialCallout(),
        ];
    }

    private static function proximityCallout(): Callout
    {
        return Callout::make('Absence proche de la précédente')
            ->description(fn (?Absence $record): ?string => $record
                ? app(AbsenceNotifier::class)->getProximityAlert($record)
                : null)
            ->icon(Heroicon::ExclamationTriangle)
            ->warning()
            ->visible(fn (?Absence $record): bool => self::isAdmin()
                && $record !== null
                && app(AbsenceNotifier::class)->getProximityAlert($record) !== null);
    }

    private static function cesiCallout(): Callout
    {
        return Callout::make('Encodage CESI requis')
            ->description(fn (?Absence $record): ?string => $record
                ? app(AbsenceNotifier::class)->getCesiAlert($record)
                : null)
            ->icon(Heroicon::InformationCircle)
            ->info()
            ->visible(fn (?Absence $record): bool => self::isAdmin()
                && $record !== null
                && app(AbsenceNotifier::class)->getCesiAlert($record) !== null);
    }

    private static function workPotentialCallout(): Callout
    {
        return Callout::make('Potentiel de travail à demander')
            ->description(fn (?Absence $record): ?string => $record
                ? app(AbsenceNotifier::class)->getWorkPotentialAlert($record)
                : null)
            ->icon(Heroicon::ShieldExclamation)
            ->danger()
            ->visible(fn (?Absence $record): bool => self::isAdmin()
                && $record !== null
                && app(AbsenceNotifier::class)->getWorkPotentialAlert($record) !== null);
    }

    private static function isAdmin(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && ($user->isAdministrator() || $user->hasRole(RolesEnum::ROLE_GRH_ADMIN->value));
    }
}
