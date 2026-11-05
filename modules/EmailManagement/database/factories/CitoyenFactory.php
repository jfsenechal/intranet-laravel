<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Database\Factories;

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

#[UseModel(Citoyen::class)]
final class CitoyenFactory extends Factory
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
        $uid = preg_replace('/[^a-z0-9.]/', '', Str::lower(Str::ascii($givenName).'.'.Str::ascii($sn)));

        return [
            'uid' => $uid.fake()->unique()->numberBetween(1, 9999),
            'mail' => fn (array $attributes): string => "{$attributes['uid']}@marche.be",
            'givenName' => $givenName,
            'sn' => $sn,
            'l' => 'Marche-en-Famenne',
            'postalAddress' => fake()->optional()->streetAddress(),
            'employeeNumber' => fake()->numerify('###########'),
            'postalCode' => '6900',
            'homeDirectory' => fn (array $attributes): string => Citoyen::homeDirectoryFor($attributes['mail']),
            'last_connection' => fake()->optional()->dateTimeBetween('-2 years'),
            'auth_token' => Str::random(64),
            'recovery_email' => null,
            'recovery_phone' => null,
        ];
    }

    /**
     * citoyens.mail is a foreign key on virtual_users.username, so the login has to exist
     * before the citoyen is inserted.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Citoyen $citoyen): void {
            if (! VirtualUser::query()->where('username', $citoyen->mail)->exists()) {
                VirtualUser::factory()->create(['username' => $citoyen->mail]);
            }
        });
    }
}
