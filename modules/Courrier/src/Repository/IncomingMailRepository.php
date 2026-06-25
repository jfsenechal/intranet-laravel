<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class IncomingMailRepository
{
    public static function findByDateAndNotNotified(string $mailDate): Builder
    {
        return IncomingMail::query()
            ->where('is_notified', false)
            ->when($mailDate, function (Builder $query) use ($mailDate): void {
                $query->whereDate('mail_date', $mailDate);
            })
            ->with(['services', 'recipients', 'attachments', 'category']);
    }

    /**
     * Limit the listing to the current day's mail the user is allowed to see.
     *
     * The model's global DepartmentScope already restricts administrators (all
     * mail) and index/admin users (their departments). Regular users without a
     * viewable department are further limited to mail they receive or that
     * targets one of their services.
     */
    public static function scopeToTodayForCurrentUser(Builder $query): Builder
    {
        $query->whereDate('incoming_mails.mail_date', today());

        $user = auth()->user();

        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isAdministrator() || $user->getCourrierViewableDepartments() !== []) {
            return $query;
        }

        $recipient = Recipient::query()->where('username', $user->username)->first();

        if ($recipient === null) {
            return $query->whereRaw('1 = 0');
        }

        $serviceIds = $recipient->services()->pluck('courrier_services.id')->all();

        return $query->where(function (Builder $inner) use ($recipient, $serviceIds): void {
            $inner->whereHas('recipients', fn (Builder $relation): Builder => $relation->whereKey($recipient->id));

            if ($serviceIds !== []) {
                $inner->orWhereHas('services', fn (Builder $relation): Builder => $relation->whereIn('courrier_services.id', $serviceIds));
            }
        });
    }

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
                        $serviceIds = $recipient->services()->pluck('courrier_services.id');
                        $q->whereIn('courrier_services.id', $serviceIds);
                    });
            })
            ->get();
    }

    public function recipientHasIndexRole(Recipient $recipient): bool
    {
        if (! $recipient->username) {
            return false;
        }

        $user = User::query()
            ->where('username', $recipient->username)
            ->first();

        if (! $user) {
            return false;
        }

        return $user->hasOneOfThisRoles([
            RolesEnum::ROLE_INDICATEUR_VILLE_INDEX->value,
            RolesEnum::ROLE_INDICATEUR_CPAS_INDEX->value,
            RolesEnum::ROLE_INDICATEUR_BOURGMESTRE_INDEX->value,
        ]);
    }
}
