<?php

namespace AcMarche\Security\Database\Seeders;

use AcMarche\Security\Constant\RoleEnum;
use AcMarche\Security\Models\Module;
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
            'name' => RoleEnum::INTRANET_ADMIN->value,
            'module_id' => null,
        ]);

        User::factory()
            ->hasAttached($adminRole)
            ->create([
                'first_name' => 'Jf',
                'last_name' => 'Sénéchal',
                'email' => 'jf@marche.be',
                'username' => 'jfsenechal',
                'password' => Hash::make('marge'),
            ]);

        User::factory()
            ->create([
                'password' => Hash::make('marge'),
            ]);

        $documentModule = Module::factory()
            ->create([
                'name' => 'Document',
            ]);

        $moduleRole = Role::factory()
            ->create([
                'name' => 'ROLE_DOCUMENT_ADMIN',
                'description' => 'Administrateur des documents',
                'module_id' => $documentModule->id,
            ]);
    }
}
