<?php

namespace AcMarche\Courrier\Database\Factories;

use AcMarche\Courrier\Models\Sender;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

final class SenderFactory extends Factory
{
    protected $model = Sender::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'department' => null
        ];
    }
}
