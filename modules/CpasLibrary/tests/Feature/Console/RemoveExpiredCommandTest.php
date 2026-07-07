<?php

declare(strict_types=1);

use AcMarche\CpasLibrary\Enums\FicheTypeEnum;
use AcMarche\CpasLibrary\Models\Fiche;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertModelExists;
use function Pest\Laravel\assertModelMissing;

it('removes expired absence fiches', function (): void {
    $expired = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'date_end' => Carbon::today()->subDay(),
    ]);

    $this->artisan('cpas-library:remove-expired')->assertSuccessful();

    assertModelMissing($expired);
});

it('keeps absence fiches ending today or later', function (): void {
    $endingToday = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'date_end' => Carbon::today(),
    ]);
    $future = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'date_end' => Carbon::today()->addWeek(),
    ]);

    $this->artisan('cpas-library:remove-expired')->assertSuccessful();

    assertModelExists($endingToday);
    assertModelExists($future);
});

it('ignores non-absence fiches even when expired', function (): void {
    $expiredDefault = Fiche::factory()->create([
        'type' => FicheTypeEnum::DEFAULT->value,
        'date_end' => Carbon::today()->subYear(),
    ]);

    $this->artisan('cpas-library:remove-expired')->assertSuccessful();

    assertModelExists($expiredDefault);
});

it('ignores absence fiches without an end date', function (): void {
    $openEnded = Fiche::factory()->create([
        'type' => FicheTypeEnum::ABSENCE->value,
        'date_end' => null,
    ]);

    $this->artisan('cpas-library:remove-expired')->assertSuccessful();

    assertModelExists($openEnded);
});
