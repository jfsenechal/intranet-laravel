<?php

namespace AcMarche\Security\Database\Seeders;

use AcMarche\Security\Constant\RoleEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $adminRole = Role::factory()->create([
            'name' => RoleEnum::ADMIN->value,
        ]);

        foreach (RoleEnum::cases() as $role) {
            if ($role !== RoleEnum::ADMIN) {
                Role::factory()->create([
                    'name' => $role->value,
                ]);
            }
        }

        User::factory()
            ->hasAttached($adminRole)
            ->create([
                'first_name' => 'Jf',
                'last_name' => 'Sénéchal',
                'email' => 'jf@marche.be',
                'username' => 'jfsenechal',
                'password' => Hash::make('marge'),
            ]);

    }
}
