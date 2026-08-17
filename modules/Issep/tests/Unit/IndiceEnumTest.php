<?php

declare(strict_types=1);

use AcMarche\Issep\Enums\IndiceEnum;

describe('reading a value from the API', function (): void {
    it('maps a known index', function (): void {
        expect(IndiceEnum::fromAqiValue(3))->toBe(IndiceEnum::GOOD)
            ->and(IndiceEnum::fromAqiValue('3'))->toBe(IndiceEnum::GOOD);
    });

    /**
     * The index is what colours the whole table, so an unexpected value degrades to "no data"
     * rather than throwing and taking the page down with it.
     */
    it('degrades an unusable value to no data', function (mixed $value): void {
        expect(IndiceEnum::fromAqiValue($value))->toBe(IndiceEnum::NO_DATA);
    })->with([null, '', 'abc', 42, -7]);

    it('keeps the API’s own invalid marker', function (): void {
        expect(IndiceEnum::fromAqiValue(-1))->toBe(IndiceEnum::NO_VALID);
    });
});

describe('presentation', function (): void {
    it('labels every index in French', function (): void {
        expect(IndiceEnum::EXCELLENT->label())->toBe('Excellent')
            ->and(IndiceEnum::AVERAGE->label())->toBe('Moyen')
            ->and(IndiceEnum::APPALLING->label())->toBe('Exécrable')
            ->and(IndiceEnum::NO_VALID->label())->toBe('Non valide');
    });

    it('gives every index a hex colour', function (IndiceEnum $indice): void {
        expect($indice->hex())->toMatch('/^#[0-9A-F]{6}$/');
    })->with(IndiceEnum::cases());

    it('names a colour per index, sharing one grey for both invalid values', function (): void {
        expect(IndiceEnum::GOOD->colorName())->toBe('belaqi-3')
            ->and(IndiceEnum::NO_DATA->colorName())->toBe('belaqi-none')
            ->and(IndiceEnum::NO_VALID->colorName())->toBe('belaqi-none');
    });

    it('registers a palette for every colour name', function (): void {
        $colors = IndiceEnum::panelColors();

        expect($colors)->toHaveKey('belaqi-3')
            ->and($colors)->toHaveKey('belaqi-none')
            ->and($colors)->toHaveCount(11)
            ->and($colors['belaqi-3'])->toBeArray();
    });

    it('groups the indices into the traffic light of the map', function (): void {
        expect(IndiceEnum::FAIRLY_GOOD->trafficLight())->toBe('green')
            ->and(IndiceEnum::AVERAGE->trafficLight())->toBe('yellow')
            ->and(IndiceEnum::POOR->trafficLight())->toBe('red')
            ->and(IndiceEnum::NO_DATA->trafficLight())->toBe('grey');
    });

    it('knows which values are an actual reading', function (): void {
        expect(IndiceEnum::EXCELLENT->hasReading())->toBeTrue()
            ->and(IndiceEnum::NO_DATA->hasReading())->toBeFalse()
            ->and(IndiceEnum::NO_VALID->hasReading())->toBeFalse();
    });
});
