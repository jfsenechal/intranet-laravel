<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Enums\DayTypeEnum;
use AcMarche\Hrm\Enums\LocationTypeEnum;
use AcMarche\Hrm\Enums\WeekdayEnum;
use AcMarche\Hrm\Models\Telework;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Telework>
 */
final class TeleworkFactory extends Factory
{
    #[Override]
    protected $model = Telework::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'regulation_agreement' => true,
            'it_agreement' => true,
            'street' => fake()->streetAddress(),
            'postal_code' => fake()->postcode(),
            'locality' => fake()->city(),
            'location_type' => LocationTypeEnum::Domicile->value,
            'day_type' => DayTypeEnum::Fixe->value,
            'fixed_day' => WeekdayEnum::Lundi->value,
            'user_add' => 'tester',
        ];
    }
}
