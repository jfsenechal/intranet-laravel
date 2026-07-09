<?php

declare(strict_types=1);

namespace AcMarche\App\Database\Factories;

use AcMarche\App\Models\Rss;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<Rss>
 */
final class RssFactory extends Factory
{
    #[Override]
    protected $model = Rss::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fn (): string => User::factory()->create()->username,
            'name' => fake()->words(3, true),
            'url' => fake()->unique()->url(),
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (): array => [
            'username' => $user->username,
        ]);
    }
}
