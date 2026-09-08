<?php

declare(strict_types=1);

namespace AcMarche\Ad\Services;

use AcMarche\Ad\Models\Subscriber;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Builder;

final class SubscriptionService
{
    /**
     * Look up an active "Agent" employee by professional or private email.
     */
    public function findEligibleEmployee(string $email): ?Employee
    {
        $email = mb_strtolower(mb_trim($email));

        if ($email === '') {
            return null;
        }

        return $this->eligibleEmployeesQuery()
            ->where(function (Builder $query) use ($email): void {
                $query->whereRaw('LOWER(professional_email) = ?', [$email])
                    ->orWhereRaw('LOWER(private_email) = ?', [$email]);
            })
            ->first();
    }

    /**
     * Every employee allowed to subscribe: an active "Agent" still under contract.
     *
     * @return Builder<Employee>
     */
    public function eligibleEmployeesQuery(): Builder
    {
        return Employee::query()
            ->where('status', StatusEnum::AGENT->value)
            ->whereHas('activeContracts');
    }

    /**
     * Eligible employees the intranet can only reach on their private address,
     * because no professional email was ever encoded for them.
     *
     * @return Builder<Employee>
     */
    public function eligibleEmployeesWithoutProfessionalEmailQuery(): Builder
    {
        return $this->eligibleEmployeesQuery()
            ->where(function (Builder $query): void {
                $query->whereNull('professional_email')
                    ->orWhere('professional_email', '');
            })
            ->whereNotNull('private_email')
            ->where('private_email', '!=', '');
    }

    public function subscribe(string $email): Subscriber
    {
        $employee = $this->findEligibleEmployee($email);

        if (! $employee instanceof Employee) {
            throw new SubscriptionException(
                "Cet email n'est pas reconnu. Merci de contacter le service RH.",
            );
        }

        return Subscriber::query()->updateOrCreate(
            ['email' => mb_strtolower(mb_trim($email))],
            [
                'first_name' => (string) $employee->first_name,
                'last_name' => (string) $employee->last_name,
            ],
        );
    }

    public function unsubscribe(string $email): bool
    {
        $email = mb_strtolower(mb_trim($email));

        if ($email === '') {
            return false;
        }

        $subscriber = Subscriber::query()->where('email', $email)->first();

        if (! $subscriber instanceof Subscriber) {
            return false;
        }

        return (bool) $subscriber->delete();
    }
}
