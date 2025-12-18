<?php

namespace AcMarche\Mileage\Handler;

use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use AcMarche\Mileage\Repository\PersonalInformationRepository;
use App\Models\User;
use Illuminate\Support\Collection;

class DeclarationHandler
{
    /**
     * Create declarations from trips grouped by applicable rate period.
     * Each declaration will contain trips that fall within the same rate period.
     *
     * @param array<Trip>|Collection<int, Trip> $trips
     * @return Collection<int, Declaration>
     * @throws \Exception
     */
    public static function handleTrips(array|Collection $trips, User $user, BudgetArticle $budgetArticle): Collection
    {
        $trips = collect($trips);

        if ($trips->isEmpty()) {
            return collect();
        }

        // Get all rates ordered by start_date
        $rates = Rate::query()
            ->orderBy('start_date')
            ->get();

        $personalInformation = PersonalInformationRepository::getByCurrentUser()->first();
        if (!$personalInformation) {
            throw new \Exception('Remplissez vos données personnelles');
        }
        // Group trips by their applicable rate based on departure_date
        $groupedByRate = $trips->groupBy(function (Trip $trip) use ($rates) {
            $rate = $rates->first(function (Rate $rate) use ($trip) {
                return $trip->departure_date >= $rate->start_date
                    && $trip->departure_date <= $rate->end_date;
            });

            return $rate?->id ?? 'no_rate';
        });

        $declarations = collect();

        // Create a declaration for each rate period
        foreach ($groupedByRate as $rateId => $tripsInPeriod) {
            // Skip trips without a matching rate
            if ($rateId === 'no_rate') {
                continue;
            }

            $rate = $rates->firstWhere('id', $rateId);

            // Create the declaration with user and rate data
            $declaration = Declaration::create([
                'type_movement' => $user->first_name,
                'last_name' => $user->last_name,
                'first_name' => $user->first_name,
                'postal_code' => $personalInformation->postal_code,
                'street' => $personalInformation->street,
                'city' => $personalInformation->city,
                'car_license_plate1' => $personalInformation->car_license_plate1,
                'car_license_plate2' => $personalInformation->car_license_plate2,
                'college_date' => $personalInformation->college_date,
                'budget_article' => $budgetArticle,
                'rate' => $rate->amount,
                'rate_omnium' => $rate->omnium,
                'omnium' => $personalInformation->omnium,
                'user_add' => $user->username,
                'departments' => json_encode(self::getDepartmentsForUser($user)),
            ]);

            // Attach trips to this declaration
            $tripIds = $tripsInPeriod->pluck('id')->toArray();
            Trip::whereIn('id', $tripIds)->update(['declaration_id' => $declaration->id]);

            // Reload trips relationship
            $declaration->load('trips');

            $declarations->push($declaration);
        }

        return $declarations;
    }

    public function populateDeclaration(
        Declaration $declaration,
        array $deplacements,
        User $user,
        Profile $profile
    ): Declaration {
        if (!$tarif = $this->tarifRepository->getTarifByDate($deplacements[0]->getDateDepart())) {
            throw new Exception('Aucun tarif pour la date '.$deplacements[0]->getDateDepart()->format('d-m-Y'));
        }

        foreach ($deplacements as $deplacement) {
            $deplacement->setDeclaration($declaration);
            $this->deplacementRepository->persist($deplacement);
        }

        $declaration->setUser($user->getUserIdentifier());
        $declaration->setNom($user->getNom());
        $declaration->setPrenom($user->getPrenom());
        $declaration->setCodePostal($profile->getCodePostal());
        $declaration->setIban($profile->getIban());
        $declaration->setLocalite($profile->getLocalite());
        $declaration->setRue($profile->getRue());
        $declaration->setPlaque1($profile->getPlaque1());
        if ('' !== $profile->getPlaque2()) {
            $declaration->setPlaque2($profile->getPlaque2());
        }

        $omnium = $profile->getOmnium() ? 1 : 0;
        $declaration->setOmnium($omnium);
        $declaration->setTarif($tarif->getMontant());
        $declaration->setTarifOmnium($tarif->getOmnium());
        $dateCollege = $profile->getDeplacementDateCollege();
        if ($dateCollege instanceof DateTimeInterface) {
            $declaration->setDateCollege($dateCollege);
        }

        return $declaration;
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
