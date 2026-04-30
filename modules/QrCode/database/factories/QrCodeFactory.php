<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Database\Factories;

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Models\QrCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Override;

/**
 * @extends Factory<QrCode>
 */
final class QrCodeFactory extends Factory
{
    #[Override]
    protected $model = QrCode::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username' => fake()->userName(),
            'name' => fake()->words(3, true),
            'action' => QrCodeActionEnum::URL,
            'message' => fake()->url(),
            'color' => '#000000',
            'background_color' => '#FFFFFF',
            'pixels' => 400,
            'format' => 'SVG',
            'style' => 'square',
            'margin' => 10,
            'label_color' => '#000000',
            'label_size' => 16,
            'label_alignment' => 'center',
            'encryption' => 'WPA',
            'network_hidden' => false,
        ];
    }

    public function url(string $url = 'https://example.com'): self
    {
        return $this->state(fn (): array => [
            'action' => QrCodeActionEnum::URL,
            'message' => $url,
        ]);
    }

    public function wifi(): self
    {
        return $this->state(fn (): array => [
            'action' => QrCodeActionEnum::WIFI,
            'ssid' => fake()->word(),
            'password' => fake()->password(),
            'encryption' => 'WPA',
            'network_hidden' => false,
        ]);
    }
}
