<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Override;

/**
 * @extends Factory<Employee>
 */
final class EmployeeFactory extends Factory
{
    #[Override]
    protected $model = Employee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $lastName = fake()->lastName();
        $firstName = fake()->firstName();

        return [
            'uuid' => (string) Str::uuid(),
            'last_name' => $lastName,
            'first_name' => $firstName,
            'slug' => Str::slug($lastName.' '.$firstName).'-'.fake()->unique()->numberBetween(1, 999_999),
            'civility' => fake()->randomElement(['monsieur', 'madame']),
            'status' => StatusEnum::AGENT->value,
            'is_archived' => false,
            'is_new_hire' => false,
            'show_birthday' => true,
            'mail_count' => 0,
            'user_add' => 'tester',
        ];
    }
}
