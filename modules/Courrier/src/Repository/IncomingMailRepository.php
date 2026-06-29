<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Models\Recipient;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

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
     * Mail the recipient should be notified about for the given date.
     *
     * Index-role recipients receive every unnotified mail, but only within the
     * department(s) they may view. The department filter is derived from the
     * recipient's own user (not the request-scoped global scope), so it is
     * correct whether the caller is an authenticated admin previewing or the
     * queued notification job running without an authenticated user.
     *
     * Other recipients receive only mail addressed to them or to one of their
     * services.
     *
     * @return Collection<int, IncomingMail>
     */
    public function getIncomingMailsForRecipient(Recipient $recipient, CarbonInterface $mailDate): Collection
    {
        $baseQuery = IncomingMail::query()
            ->where('is_notified', false)
            ->whereDate('mail_date', $mailDate)
            ->with(['services', 'recipients', 'attachments', 'category']);

        if ($this->recipientHasIndexRole($recipient)) {
            $departments = $this->viewableDepartmentValuesForRecipient($recipient);

            return $baseQuery
                ->withoutGlobalScope(DepartmentScope::class)
                ->when(
                    $departments !== [],
                    fn (Builder $query): Builder => $query->whereIn('incoming_mails.department', $departments),
                )
                ->get();
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
        $user = $this->findUserForRecipient($recipient);

        if (! $user instanceof User) {
            return false;
        }

        return Gate::forUser($user)->check('courrier-index');
    }

    private function findUserForRecipient(Recipient $recipient): ?User
    {
        if (! $recipient->username) {
            return null;
        }

        return User::query()
            ->where('username', $recipient->username)
            ->first();
    }

    /**
     * Department values the recipient may view, derived from their user roles.
     *
     * Returns an empty array for administrators (who may view every department),
     * which leaves the query unfiltered.
     *
     * @return list<string>
     */
    private function viewableDepartmentValuesForRecipient(Recipient $recipient): array
    {
        $user = $this->findUserForRecipient($recipient);

        if (! $user instanceof User) {
            return [];
        }

        return array_map(
            fn (DepartmentCourrierEnum $department): string => $department->value,
            $user->getCourrierViewableDepartments(),
        );
    }
}
