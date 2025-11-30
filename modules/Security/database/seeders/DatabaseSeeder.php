<?php

namespace AcMarche\Security\Database\Seeders;

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
        User::find(1)
            ->update([
                'password' => Hash::make('marge'),
                'email' => config('mail.webmaster_email'),
            ]);
    }
}
