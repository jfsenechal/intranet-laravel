<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Detach roles whose module no longer exists.
     *
     * `roles.module_id` has no foreign key, so deleting a module left its roles
     * pointing at a missing row. The column is nullable and the roles are still
     * assigned to users, so they are detached rather than deleted: an orphaned
     * role keeps working, it just no longer claims to belong to a module.
     */
    public function up(): void
    {
        DB::table('roles')
            ->whereNotNull('module_id')
            ->whereNotIn('module_id', DB::table('modules')->select('id'))
            ->update(['module_id' => null]);
    }

    /**
     * Irreversible: the modules these roles referenced no longer exist.
     */
    public function down(): void
    {
        //
    }
};
