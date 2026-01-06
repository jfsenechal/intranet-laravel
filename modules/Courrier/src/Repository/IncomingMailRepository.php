<?php

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class IncomingMailRepository
{
    /**
     * @return Collection<int, IncomingMail>
     */
    public function getIncomingMailsForRecipient(Recipient $recipient, Carbon $mailDate): Collection
    {
        $baseQuery = IncomingMail::query()
            ->where('is_notified', false)
            ->whereDate('mail_date', $mailDate)
            ->with(['services', 'recipients', 'attachments', 'category']);

        if ($this->recipientHasIndexRole($recipient)) {
            return $baseQuery->get();
        }

        return $baseQuery
            ->where(function ($query) use ($recipient): void {
                $query->whereHas('recipients', function ($q) use ($recipient): void {
                    $q->where('recipients.id', $recipient->id);
                })
                    ->orWhereHas('services', function ($q) use ($recipient): void {
                        $serviceIds = $recipient->services()->pluck('services.id');
                        $q->whereIn('services.id', $serviceIds);
                    });
            })
            ->get();
    }

    public function recipientHasIndexRole(Recipient $recipient): bool
    {
        if (!$recipient->username) {
            return false;
        }

        $user = User::query()
            ->where('username', $recipient->username)
            ->first();

        if (!$user) {
            return false;
        }

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value,
            RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value,
            RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->value,
        ]);
    }

}
