<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\PersonalInformation;
use AcMarche\Mileage\Service\PersonalInformationService;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create(['username' => 'testuser']);
});

describe('createPersonalInformation', function () {
    test('creates personal information for user', function () {
        $data = [
            'omnium' => true,
            'college_trip_date' => '2024-06-15',
        ];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();

        expect($personalInfo)->not->toBeNull()
            ->and($personalInfo->username)->toBe($this->user->username)
            ->and($personalInfo->omnium)->toBeTrue()
            ->and($personalInfo->college_trip_date)->toBe('2024-06-15');
    });

    test('creates personal information with default values', function () {
        $data = [];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();

        expect($personalInfo)->not->toBeNull()
            ->and($personalInfo->username)->toBe($this->user->username)
            ->and($personalInfo->omnium)->toBeFalse()
            ->and($personalInfo->college_trip_date)->toBeNull();
    });

    test('creates personal information with omnium false', function () {
        $data = [
            'omnium' => false,
            'college_trip_date' => '2024-01-10',
        ];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();

        expect($personalInfo)->not->toBeNull()
            ->and($personalInfo->omnium)->toBeFalse()
            ->and($personalInfo->college_trip_date)->toBe('2024-01-10');
    });

    test('updates existing personal information', function () {
        // Create initial personal information
        $existing = PersonalInformation::factory()->create([
            'username' => $this->user->username,
            'omnium' => false,
            'college_trip_date' => '2024-01-01',
        ]);

        $data = [
            'omnium' => true,
            'college_trip_date' => '2024-12-31',
        ];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        // Should still have only one record
        $count = PersonalInformation::where('username', $this->user->username)->count();
        expect($count)->toBe(1);

        // Verify it was updated
        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();
        expect($personalInfo->id)->toBe($existing->id)
            ->and($personalInfo->omnium)->toBeTrue()
            ->and($personalInfo->college_trip_date)->toBe('2024-12-31');
    });

    test('updates only omnium and college_trip_date', function () {
        // Create initial personal information with other fields
        $existing = PersonalInformation::factory()->create([
            'username' => $this->user->username,
            'omnium' => false,
            'college_trip_date' => '2024-01-01',
            'postal_code' => '6900',
            'street' => 'Rue Test',
            'city' => 'Marche',
            'iban' => 'BE68539007547034',
            'car_license_plate1' => '1-ABC-123',
        ]);

        $data = [
            'omnium' => true,
            'college_trip_date' => '2024-06-15',
        ];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();

        // Verify omnium and college_trip_date were updated
        expect($personalInfo->omnium)->toBeTrue()
            ->and($personalInfo->college_trip_date)->toBe('2024-06-15')
            // Verify other fields remain unchanged
            ->and($personalInfo->postal_code)->toBe('6900')
            ->and($personalInfo->street)->toBe('Rue Test')
            ->and($personalInfo->city)->toBe('Marche')
            ->and($personalInfo->iban)->toBe('BE68539007547034')
            ->and($personalInfo->car_license_plate1)->toBe('1-ABC-123');
    });

    test('handles null college_trip_date', function () {
        $data = [
            'omnium' => true,
            'college_trip_date' => null,
        ];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();

        expect($personalInfo)->not->toBeNull()
            ->and($personalInfo->omnium)->toBeTrue()
            ->and($personalInfo->college_trip_date)->toBeNull();
    });

    test('handles updating to null college_trip_date', function () {
        $existing = PersonalInformation::factory()->create([
            'username' => $this->user->username,
            'omnium' => false,
            'college_trip_date' => '2024-01-01',
        ]);

        $data = [
            'omnium' => true,
            'college_trip_date' => null,
        ];

        PersonalInformationService::createPersonalInformation($this->user, $data);

        $personalInfo = PersonalInformation::where('username', $this->user->username)->first();

        expect($personalInfo->omnium)->toBeTrue()
            ->and($personalInfo->college_trip_date)->toBeNull();
    });

    test('throws exception when creation fails', function () {
        // Mock a database issue by using an invalid connection
        $invalidUser = new User(['username' => null]);

        $data = [
            'omnium' => true,
            'college_trip_date' => '2024-06-15',
        ];

        PersonalInformationService::createPersonalInformation($invalidUser, $data);
    })->throws(Exception::class);

    test('creates personal information for multiple users', function () {
        $user1 = User::factory()->create(['username' => 'user1']);
        $user2 = User::factory()->create(['username' => 'user2']);

        $data1 = [
            'omnium' => true,
            'college_trip_date' => '2024-01-15',
        ];

        $data2 = [
            'omnium' => false,
            'college_trip_date' => '2024-06-20',
        ];

        PersonalInformationService::createPersonalInformation($user1, $data1);
        PersonalInformationService::createPersonalInformation($user2, $data2);

        $personalInfo1 = PersonalInformation::where('username', 'user1')->first();
        $personalInfo2 = PersonalInformation::where('username', 'user2')->first();

        expect($personalInfo1)->not->toBeNull()
            ->and($personalInfo1->omnium)->toBeTrue()
            ->and($personalInfo1->college_trip_date)->toBe('2024-01-15')
            ->and($personalInfo2)->not->toBeNull()
            ->and($personalInfo2->omnium)->toBeFalse()
            ->and($personalInfo2->college_trip_date)->toBe('2024-06-20');
    });
});
