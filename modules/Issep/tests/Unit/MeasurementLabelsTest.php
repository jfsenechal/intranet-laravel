<?php

declare(strict_types=1);

use AcMarche\Issep\Support\MeasurementLabels;

it('describes a measurement whatever the spelling the API used', function (string $field): void {
    expect(MeasurementLabels::describe($field))->toBe('Température exprimée en °C');
})->with(['BME_t', 'bme_t', 'bmeT', 'BMET']);

it('describes the status field of a measurement', function (): void {
    expect(MeasurementLabels::describe('BME_t_statut'))
        ->toBe('Statut de la mesure: température exprimée en °C');
});

it('still describes a status field of an unknown measurement', function (): void {
    expect(MeasurementLabels::describe('Foo_statut'))->toBe('Statut de la mesure');
});

/**
 * A field with no description is displayed under its own name rather than hidden, so the page
 * shows a sensor field the data dictionary does not cover yet.
 */
it('has no description for an unknown field', function (): void {
    expect(MeasurementLabels::describe('somethingNew'))->toBeNull();
});
