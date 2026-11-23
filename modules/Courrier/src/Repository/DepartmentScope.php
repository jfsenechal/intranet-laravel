<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Repository;

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Models\IncomingMail;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Query\Builder as QueryBuilder;

final class DepartmentScope implements Scope
{
    /**
     * Department the current user administers. Used for the create auto-fill.
     */
    public static function getCurrentAdminUserDepartment(): ?DepartmentCourrierEnum
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        return $user->getCourrierAdminDepartment();
    }

    /**
     * Department the current user may assign when creating or editing mail.
     */
    public static function getAssignableDepartment(): ?DepartmentCourrierEnum
    {
        $user = auth()->user();

        if ($user === null) {
            return null;
        }

        return $user->getCourrierAdminDepartment();
    }

    /**
     * Whether the current user administers the CPAS department. Only they
     * classify their mail, so the category field, filter and column are theirs.
     *
     * Asks for the role rather than going through getAssignableDepartment(),
     * which reduces a user to a single admin department.
     */
    public static function currentUserAdministersCpas(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->hasRole(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN->value);
    }

    /**
     * Departments the current user may see. Used to scope read queries.
     *
     * @return DepartmentCourrierEnum[]
     */
    public static function getViewableDepartments(): array
    {
        $user = auth()->user();

        if ($user === null) {
            return [];
        }

        return $user->getCourrierViewableDepartments();
    }

    /**
     * Narrow a mail query to what the user receives: mail addressed to one of
     * their recipient rows, or to a service one of those rows belongs to.
     *
     * Goes through the pivot tables with subqueries rather than whereHas(), so
     * the Service department scope never filters out the user's own services.
     */
    public static function orWhereReceivedBy(Builder|QueryBuilder $query, string $username, string $table = 'incoming_mails'): void
    {
        $recipientIds = fn (QueryBuilder $recipients): QueryBuilder => $recipients
            ->select('id')
            ->from('recipients')
            ->where('username', $username);

        $query->orWhereIn($table.'.id', fn (QueryBuilder $pivot): QueryBuilder => $pivot
            ->select('incoming_mail_id')
            ->from('incoming_mail_recipient')
            ->whereIn('recipient_id', $recipientIds))
            ->orWhereIn($table.'.id', fn (QueryBuilder $pivot): QueryBuilder => $pivot
                ->select('incoming_mail_id')
                ->from('incoming_mail_service')
                ->whereIn('service_id', fn (QueryBuilder $memberships): QueryBuilder => $memberships
                    ->select('service_id')
                    ->from('recipient_service')
                    ->whereIn('recipient_id', $recipientIds)));
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * A department role widens what the user sees, it never narrows it: the
     * mail they receive themselves stays visible whatever its department, so a
     * Ville index user who is the recipient of a Cpas mail can still read it.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $departments = self::getViewableDepartments();
        if (count($departments) === 0) {
            return;
        }

        $table = $model->getTable();
        $values = array_map(fn (DepartmentCourrierEnum $d) => $d->value, $departments);

        if (! $model instanceof IncomingMail) {
            $builder->whereIn($table.'.department', $values);

            return;
        }

        $username = auth()->user()->username;

        $builder->where(function (Builder $inner) use ($table, $values, $username): void {
            $inner->whereIn($table.'.department', $values);

            if (filled($username)) {
                self::orWhereReceivedBy($inner, $username, $table);
            }
        });
    }
}
