<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Database\Factories;

use AcMarche\EmailManagement\Models\Employe;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

#[UseModel(Employe::class)]
final class EmployeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $givenName = fake()->firstName();
        $sn = fake()->lastName();
        $samAccountName = Str::lower(Str::substr(Str::ascii($givenName), 0, 1).Str::ascii($sn));
        $samAccountName = Str::limit(preg_replace('/[^a-z0-9]/', '', $samAccountName), 18, '');

        return [
            'samaccountname' => $samAccountName.fake()->unique()->numberBetween(1, 9999),
            'givenName' => $givenName,
            'sn' => $sn,
            'cn' => $givenName.' '.$sn,
            'displayName' => $givenName.' '.$sn,
            'mail' => fake()->unique()->safeEmail(),
            'dn' => fn (array $attributes): string => "CN={$attributes['cn']},OU=AC,OU=MUSERS,DC=ad,DC=marche,DC=be",
            'description' => fake()->optional()->sentence(),
            'telephoneNumber' => fake()->optional()->phoneNumber(),
            'sync_at' => now(),
        ];
    }
}
