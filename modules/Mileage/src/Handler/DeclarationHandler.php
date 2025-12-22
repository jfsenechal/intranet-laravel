<?php

namespace AcMarche\Mileage\Handler;

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;
use Illuminate\Support\Collection;

class DeclarationHandler
{
    /**
     * Create declarations from trips grouped by type_movement and rate period.
     * Each declaration will contain trips that have the same type_movement and fall within the same rate period.
     *
     * @param array<Trip>|Collection<int, Trip> $trips
     * @return Collection<int, Declaration>
     *
     * @throws \Exception
     */
    public static function handleTrips(
        array|Collection $trips,
        User $user,
        PersonalInformation $personalInformation,
        BudgetArticle $budgetArticle
    ): Collection {
        $trips = collect($trips);

        if ($trips->isEmpty()) {
            return collect();
        }

        // Get all rates ordered by start_date
        $rates = Rate::query()
            ->orderBy('start_date')
            ->get();

        // Group trips by type_movement and rate
        $groupedTrips = $trips->groupBy(function (Trip $trip) use ($rates) {
            $rate = $rates->first(function (Rate $rate) use ($trip) {
                return $trip->departure_date >= $rate->start_date
                    && $trip->departure_date <= $rate->end_date;
            });

            $rateId = $rate?->id ?? 'no_rate';
            $typeMovement = $trip->type_movement ?? 'unknown';

            return $typeMovement.'_'.$rateId;
        });

        $declarations = collect();

        // Create a declaration for each type_movement + rate combination
        foreach ($groupedTrips as $groupKey => $tripsInGroup) {
            // Skip trips without a matching rate
            if (str_ends_with($groupKey, '_no_rate')) {
                continue;
            }

            // Extract type_movement and rate_id from the group key
            $parts = explode('_', $groupKey);
            $rateId = (int)array_pop($parts);
            $typeMovement = implode('_', $parts);

            $rate = $rates->firstWhere('id', $rateId);

            // Create the declaration with user and rate data
            $declaration = Declaration::create([
                'type_movement' => $typeMovement,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'postal_code' => $personalInformation->postal_code,
                'street' => $personalInformation->street,
                'city' => $personalInformation->city,
                'iban' => $personalInformation->iban,
                'car_license_plate1' => $personalInformation->car_license_plate1,
                'car_license_plate2' => $personalInformation->car_license_plate2,
                'college_date' => $personalInformation->college_trip_date,
                'budget_article' => $budgetArticle->name,
                'rate' => $rate->amount,
                'rate_omnium' => $rate->omnium,
                'omnium' => $personalInformation->omnium,
                'user_add' => $user->username,
                'departments' => json_encode(self::getDepartmentsForUser($user)),
            ]);

            // Attach trips to this declaration
            $tripIds = $tripsInGroup->pluck('id')->toArray();
            Trip::whereIn('id', $tripIds)->update(['declaration_id' => $declaration->id]);

            // Reload trips relationship
            $declaration->load('trips');

            $declarations->push($declaration);
        }

        return $declarations;
    }

    private static function getDepartmentsForUser(User $user): array
    {
        $departments = [];
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_ADMIN->value)) {
            return RolesEnum::getRoles();
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value)) {
            $departments[] = RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value;
        }
        if ($user->hasRole(RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value)) {
            $departments[] = RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value;
        }

        return $departments;
    }
}
