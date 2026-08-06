<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Delete `role_user` rows pointing at a role that no longer exists.
     *
     * `role_user.role_id` was declared with `foreignIdFor()` and no
     * `constrained()`, so there is no foreign key and earlier role deletions
     * left their pivot rows behind. They are invisible to reads (`hasRole()`
     * joins through `roles`) but would silently grant the stale role again if
     * its id were ever reused.
     *
     * Matched by subquery rather than by a fixed id list so any orphan is
     * caught, whenever it was created.
     */
    public function up(): void
    {
        DB::table('role_user')
            ->whereNotIn('role_id', DB::table('roles')->select('id'))
            ->delete();
    }

    /**
     * Irreversible: the deleted rows reference roles that no longer exist, so
     * there is nothing to restore them to.
     */
    public function down(): void
    {
        //
    }
};
