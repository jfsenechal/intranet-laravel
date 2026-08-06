<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Legacy department roles, superseded by the `_ADMIN` / `_INDEX` / `_READ`
     * tiers which carry the department themselves.
     *
     * @var list<string>
     */
    private const ROLE_NAMES = [
        'ROLE_INDICATEUR_VILLE',
        'ROLE_INDICATEUR_CPAS',
        'ROLE_INDICATEUR_BOURGMESTRE',
    ];

    /**
     * Drop the three base indicateur roles and their assignments.
     *
     * Nothing reads them: they are absent from RolesEnum's admin/index/read
     * groupings, so getCourrierViewableDepartments() ignores them, and the
     * courrier-index / courrier-administrator gates list only the tiered roles.
     * Every user holding one also holds a tiered role, so no access is lost.
     *
     * `role_user` has no foreign-key constraint, so the pivot rows are removed
     * explicitly to avoid orphans.
     */
    public function up(): void
    {
        $roleIds = DB::table('roles')
            ->whereIn('name', self::ROLE_NAMES)
            ->pluck('id')
            ->all();

        if ($roleIds === []) {
            return;
        }

        DB::table('role_user')->whereIn('role_id', $roleIds)->delete();
        DB::table('roles')->whereIn('id', $roleIds)->delete();
    }

    /**
     * Recreate the role rows, attached to the Indicateurs module.
     *
     * The user assignments are not restored: they were redundant with the
     * tiered roles and are not recorded anywhere to replay from.
     */
    public function down(): void
    {
        $moduleId = DB::table('modules')->where('name', 'Indicateurs')->value('id');

        foreach (self::ROLE_NAMES as $name) {
            DB::table('roles')->updateOrInsert(
                ['name' => $name],
                ['module_id' => $moduleId],
            );
        }
    }
};
