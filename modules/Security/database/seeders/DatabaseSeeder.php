<?php

declare(strict_types=1);

namespace AcMarche\Security\Database\Seeders;

use AcMarche\Pst\Enums\RoleEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->where('username', config('app.default_user.name'))
            ->update([
                'password' => Hash::make(config('app.default_user.password')),
                'email' => config('app.default_user.email'),
                'username' => config('app.default_user.name'),
                'is_administrator' => true,
            ]);
        Role::factory()->create([
            'name' => RoleEnum::ADMIN->value,
        ]);
        Role::factory()->create([
            'name' => RoleEnum::MANDATAIRE->value,
        ]);

    }
}
