<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Database\Factories;

use AcMarche\EmailManagement\Models\Citoyen;
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
            'dn' => fn (array $attributes): string => "uid={$attributes['uid']},ou=Users,ou=Citoyens,dc=marche,dc=be",
            'mail' => fn (array $attributes): string => "{$attributes['uid']}@marche.be",
            'givenName' => $givenName,
            'sn' => $sn,
            'cn' => $sn.' '.$givenName,
            'l' => 'Marche-en-Famenne',
            'postalAddress' => fake()->optional()->streetAddress(),
            'employeeNumber' => fake()->numerify('###########'),
            'postalCode' => '6900',
            'gosaMailQuota' => 150,
            'homeDirectory' => fn (array $attributes): string => '/var/spool/dovecot/mail/'.$attributes['uid'][0].'/'.$attributes['uid'],
            'gosaMailForwardingAddress' => fn (array $attributes): string => "{$attributes['uid']}@citoyen.marche.be",
            'userPassword' => '{SSHA}'.Str::random(32),
            'legacy_password' => null,
            'last_connection' => fake()->optional()->dateTimeBetween('-2 years'),
            'auth_token' => Str::random(64),
            'recovery_email' => null,
            'recovery_phone' => null,
        ];
    }
}
