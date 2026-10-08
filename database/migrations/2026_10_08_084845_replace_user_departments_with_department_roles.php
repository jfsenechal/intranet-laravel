<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Department => role, per module. The values of the News and Pst RolesEnum,
     * spelled out so the migration keeps working if the enums change later.
     *
     * @var array<string, array{module_id: int, roles: array<string, string>}>
     */
    private const DEPARTMENT_ROLES = [
        'news' => ['module_id' => 15, 'roles' => ['VILLE' => 'ROLE_NEWS_VILLE', 'CPAS' => 'ROLE_NEWS_CPAS']],
        'pst' => ['module_id' => 58, 'roles' => ['VILLE' => 'ROLE_PST_VILLE', 'CPAS' => 'ROLE_PST_CPAS']],
    ];

    /**
     * Held by every PST user, now replaced by the PST department roles.
     */
    private const PST_ROLE = 'ROLE_PST';

    /**
     * Roles whose holders work on the PST and so need a PST department role.
     *
     * @var list<string>
     */
    private const PST_USER_ROLES = ['ROLE_PST', 'ROLE_PST_ADMIN', 'ROLE_PST_MANDATAIRE'];

    /**
     * Turn `users.departments` into department roles.
     *
     * Every user gets the news role of each of their departments. Only the PST
     * users get the PST role of each of their departments, which then replaces
     * ROLE_PST. The roles are created here, rather than by intranet:sync-roles,
     * because they must exist before they are assigned; the sync will skip them.
     */
    public function up(): void
    {
        $roleIds = [];
        foreach (self::DEPARTMENT_ROLES as $module => ['module_id' => $moduleId, 'roles' => $roles]) {
            foreach ($roles as $department => $name) {
                $roleIds[$module][$department] = $this->createRole($name, $moduleId);
            }
        }

        $pstUserIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('roles.name', self::PST_USER_ROLES)
            ->pluck('role_user.user_id')
            ->flip();

        DB::table('users')->select(['id', 'departments'])->orderBy('id')->each(
            function (object $user) use ($roleIds, $pstUserIds): void {
                $rows = [];
                foreach ($this->departmentsOf($user->departments) as $department) {
                    $rows[] = ['user_id' => $user->id, 'role_id' => $roleIds['news'][$department]];

                    if ($pstUserIds->has($user->id)) {
                        $rows[] = ['user_id' => $user->id, 'role_id' => $roleIds['pst'][$department]];
                    }
                }

                DB::table('role_user')->insertOrIgnore($rows);
            },
        );

        $pstRoleId = DB::table('roles')->where('name', self::PST_ROLE)->value('id');
        if ($pstRoleId !== null) {
            DB::table('role_user')->where('role_id', $pstRoleId)->delete();
            DB::table('roles')->where('id', $pstRoleId)->delete();
        }
    }

    /**
     * Give ROLE_PST back to the holders of a PST department role, then drop
     * the department roles. The departments themselves are rebuilt from these
     * roles by the column drop migration, which is rolled back first.
     */
    public function down(): void
    {
        $pstRoleId = $this->createRole(self::PST_ROLE, self::DEPARTMENT_ROLES['pst']['module_id']);

        $departmentRoleIds = DB::table('roles')
            ->whereIn('name', [
                ...array_values(self::DEPARTMENT_ROLES['news']['roles']),
                ...array_values(self::DEPARTMENT_ROLES['pst']['roles']),
            ])
            ->pluck('id');

        $pstUserIds = DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('roles.name', array_values(self::DEPARTMENT_ROLES['pst']['roles']))
            ->distinct()
            ->pluck('role_user.user_id');

        DB::table('role_user')->insertOrIgnore(
            $pstUserIds->map(fn (int $userId): array => ['user_id' => $userId, 'role_id' => $pstRoleId])->all(),
        );

        DB::table('role_user')->whereIn('role_id', $departmentRoleIds)->delete();
        DB::table('roles')->whereIn('id', $departmentRoleIds)->delete();
    }

    /**
     * Create the role when missing and return its id. It is attached to its
     * module only when that module exists, `roles.module_id` being a foreign key.
     */
    private function createRole(string $name, int $moduleId): int
    {
        $existingId = DB::table('roles')->where('name', $name)->value('id');
        if ($existingId !== null) {
            return (int) $existingId;
        }

        return (int) DB::table('roles')->insertGetId([
            'name' => $name,
            'module_id' => DB::table('modules')->where('id', $moduleId)->exists() ? $moduleId : null,
        ]);
    }

    /**
     * The known departments of the raw json column, tolerating null or invalid
     * values left by the legacy data.
     *
     * @return list<string>
     */
    private function departmentsOf(?string $departments): array
    {
        $decoded = json_decode((string) $departments, true);

        return array_values(array_intersect(
            ['VILLE', 'CPAS'],
            is_array($decoded) ? $decoded : [],
        ));
    }
};
