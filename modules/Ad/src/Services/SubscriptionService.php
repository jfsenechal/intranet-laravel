<?php

declare(strict_types=1);

namespace AcMarche\Ad\Services;

use AcMarche\Ad\Models\Subscriber;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Employee;

final class SubscriptionService
{
    /**
     * Look up an active "Agent" employee by professional or private email.
     */
    public function findEligibleEmployee(string $email): ?Employee
    {
        $email = mb_strtolower(trim($email));

        if ($email === '') {
            return null;
        }

        return Employee::query()
            ->where('status', StatusEnum::AGENT->value)
            ->where(function ($query) use ($email): void {
                $query->whereRaw('LOWER(professional_email) = ?', [$email])
                    ->orWhereRaw('LOWER(private_email) = ?', [$email]);
            })
            ->whereHas('activeContracts')
            ->first();
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
            ['email' => mb_strtolower(trim($email))],
            [
                'first_name' => (string) $employee->first_name,
                'last_name' => (string) $employee->last_name,
            ],
        );
    }

    public function unsubscribe(string $email): bool
    {
        $email = mb_strtolower(trim($email));

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
