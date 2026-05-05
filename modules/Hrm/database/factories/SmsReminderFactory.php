<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Database\Factories;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\SmsReminder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<SmsReminder>
 */
final class SmsReminderFactory extends Factory
{
    #[Override]
    protected $model = SmsReminder::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'phone_number' => '32476'.fake()->numerify('######'),
            'message' => fake()->sentence(),
            'reminder_date' => fake()->dateTimeBetween('now', '+1 month'),
        ];
    }
}
