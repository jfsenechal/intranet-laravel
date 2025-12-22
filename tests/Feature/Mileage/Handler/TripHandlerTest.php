<?php

declare(strict_types=1);

use AcMarche\Mileage\Enums\TypeMovementEnum;
use AcMarche\Mileage\Handler\TripHandler;
use AcMarche\Mileage\Models\Rate;
use AcMarche\Mileage\Models\Trip;
use App\Models\User;

beforeEach(function () {
    $this->handler = new TripHandler();
    $this->user = User::factory()->create();
});

describe('setRate', function () {
    test('sets rate and omnium from matching rate period', function () {
        $rate = Rate::factory()->create([
            'amount' => 0.44,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-06-15',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        expect($trip->rate)->toBe('0.44')
            ->and($trip->omnium)->toBe('0.03');
    });

    test('uses most recent rate when multiple rates match', function () {
        // Create older rate
        Rate::factory()->create([
            'amount' => 0.35,
            'omnium' => 0.02,
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-31',
        ]);

        // Create newer rate
        $newerRate = Rate::factory()->create([
            'amount' => 0.45,
            'omnium' => 0.04,
            'start_date' => '2024-07-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-08-15',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        // Should use the more recent rate
        expect($trip->rate)->toBe('0.45')
            ->and($trip->omnium)->toBe('0.04');
    });

    // Note: end_date is now required in the database schema, so null end_date is not supported

    test('does not set rate when no matching rate exists', function () {
        Rate::factory()->create([
            'amount' => 0.40,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2025-12-31',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        expect($trip->rate)->toBeNull()
            ->and($trip->omnium)->toBeNull();
    });

    test('handles trip at start of rate period', function () {
        $rate = Rate::factory()->create([
            'amount' => 0.40,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-01-01',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        expect($trip->rate)->toBe('0.40')
            ->and($trip->omnium)->toBe('0.03');
    });

    test('handles trip at end of rate period', function () {
        $rate = Rate::factory()->create([
            'amount' => 0.40,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-12-31',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        expect($trip->rate)->toBe('0.40')
            ->and($trip->omnium)->toBe('0.03');
    });

    test('does not match trip before rate start_date', function () {
        Rate::factory()->create([
            'amount' => 0.40,
            'omnium' => 0.03,
            'start_date' => '2024-06-01',
            'end_date' => '2024-12-31',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-05-31',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        expect($trip->rate)->toBeNull()
            ->and($trip->omnium)->toBeNull();
    });

    test('does not match trip after rate end_date', function () {
        Rate::factory()->create([
            'amount' => 0.40,
            'omnium' => 0.03,
            'start_date' => '2024-01-01',
            'end_date' => '2024-06-30',
        ]);

        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-07-01',
            'rate' => null,
            'omnium' => null,
        ]);

        $this->handler->setRate($trip);

        expect($trip->rate)->toBeNull()
            ->and($trip->omnium)->toBeNull();
    });
});

describe('setTypeOfMovement', function () {
    test('sets type to EXTERNAL when arrival_date is present', function () {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-06-15',
            'arrival_date' => '2024-06-16',
            'type_movement' => null,
        ]);

        $this->handler->setTypeOfMovement($trip);

        expect($trip->type_movement)->toBe(TypeMovementEnum::EXTERNAL->value);
    });

    test('sets type to INTERNAL when arrival_date is null', function () {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-06-15',
            'arrival_date' => null,
            'type_movement' => null,
        ]);

        $this->handler->setTypeOfMovement($trip);

        expect($trip->type_movement)->toBe(TypeMovementEnum::INTERNAL->value);
    });

    test('overwrites existing type_movement when arrival_date is present', function () {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-06-15',
            'arrival_date' => '2024-06-16',
            'type_movement' => 'interne',
        ]);

        $this->handler->setTypeOfMovement($trip);

        expect($trip->type_movement)->toBe(TypeMovementEnum::EXTERNAL->value);
    });

    test('overwrites existing type_movement when arrival_date is null', function () {
        $trip = Trip::factory()->create([
            'user_id' => $this->user->id,
            'departure_date' => '2024-06-15',
            'arrival_date' => null,
            'type_movement' => 'externe',
        ]);

        $this->handler->setTypeOfMovement($trip);

        expect($trip->type_movement)->toBe(TypeMovementEnum::INTERNAL->value);
    });
});
