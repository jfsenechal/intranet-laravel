<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Department role => department, to rebuild the column on rollback.
     *
     * @var array<string, string>
     */
    private const ROLE_DEPARTMENTS = [
        'ROLE_NEWS_VILLE' => 'VILLE',
        'ROLE_NEWS_CPAS' => 'CPAS',
        'ROLE_PST_VILLE' => 'VILLE',
        'ROLE_PST_CPAS' => 'CPAS',
    ];

    /**
     * The departments are now held as News and Pst roles, see the previous migration.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'departments')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('departments');
        });
    }

    /**
     * Re-add the column and fill it from the department roles each user holds.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'departments')) {
            return;
        }

        Schema::table('users', function (Blueprint $table): void {
            $table->json('departments')->nullable(false)->default('[]');
        });

        DB::table('role_user')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->whereIn('roles.name', array_keys(self::ROLE_DEPARTMENTS))
            ->get(['role_user.user_id', 'roles.name'])
            ->groupBy('user_id')
            ->each(function ($roles, int $userId): void {
                $departments = $roles
                    ->map(fn (object $role): string => self::ROLE_DEPARTMENTS[$role->name])
                    ->unique()
                    ->values()
                    ->all();

                DB::table('users')->where('id', $userId)->update(['departments' => json_encode($departments)]);
            });
    }
};
