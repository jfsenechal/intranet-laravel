<?php

declare(strict_types=1);

namespace AcMarche\Pst\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * Pst models are bound to the maria-pst connection, and Eloquent copies that connection
 * onto the related User model because it declares none of its own. The users table then
 * gets looked up in the pst database instead of the intranet one, so it has to be
 * qualified for the join to resolve on a single connection.
 */
trait QualifiedUsersTableTrait
{
    /**
     * The table has to be set on the related model and not only on the relation query:
     * whereHas() and friends build their existence subquery from a fresh
     * newQueryWithoutRelationships(), which never sees the relation's own from().
     */
    protected function withQualifiedUsersTable(BelongsToMany $relation): BelongsToMany
    {
        $table = $this->qualifiedUsersTable();
        $relation->getRelated()->setTable($table);

        return $relation->tap(fn ($query): mixed => $query->from($table));
    }

    /**
     * Dotted rather than raw, so the query grammar keeps wrapping both segments.
     */
    protected function qualifiedUsersTable(): string
    {
        $user = new User();
        $table = $user->getTable();
        $database = DB::connection($user->getConnectionName())->getDatabaseName();

        if ($database === DB::connection($this->getConnectionName())->getDatabaseName()) {
            return $table;
        }

        return $database.'.'.$table;
    }
}
