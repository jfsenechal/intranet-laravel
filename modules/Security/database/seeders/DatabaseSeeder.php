<?php

declare(strict_types=1);

namespace AcMarche\Security\Database\Seeders;

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Providers\PstServiceProvider;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->backfillInvalidDepartments();

        User::query()->where('username', config('app.default_user.name'))
            ->update([
                'password' => Hash::make(config('app.default_user.password')),
                'email' => config('app.default_user.email'),
                'username' => config('app.default_user.name'),
                'departments' => [DepartmentEnum::VILLE->value],
                'is_administrator' => true,
            ]);
        Role::create([
            'name' => RolesEnum::ADMIN->value,
            'module_id' => PstServiceProvider::$module_id,
        ]);
        Role::create([
            'name' => RolesEnum::MANDATAIRE->value,
            'module_id' => PstServiceProvider::$module_id,
        ]);

    }

    /**
     * Repair rows whose JSON `departments` column is null or invalid, which would
     * otherwise make MariaDB's implicit `json_valid(departments)` CHECK fail on
     * any subsequent update of those rows.
     */
    private function backfillInvalidDepartments(): void
    {
        DB::table('users')
            ->whereRaw('departments IS NULL OR JSON_VALID(departments) = 0')
            ->update(['departments' => json_encode([DepartmentEnum::VILLE->value])]);
    }
}
