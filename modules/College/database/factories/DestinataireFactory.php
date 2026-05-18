<?php

declare(strict_types=1);

namespace AcMarche\College\Database\Factories;

use AcMarche\College\Models\Destinataire;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Destinataire>
 */
final class DestinataireFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nom = fake()->lastName();
        $prenom = fake()->firstName();

        return [
            'slugname' => Str::slug($nom.'_'.$prenom, '_').'_'.fake()->unique()->numberBetween(1, 99999),
            'nom' => $nom,
            'prenom' => $prenom,
            'email' => fake()->unique()->safeEmail(),
            'pv_service' => fake()->boolean(),
            'ordre_service' => fake()->boolean(),
            'ordre_college' => fake()->boolean(),
            'pv_college' => fake()->boolean(),
        ];
    }
}
