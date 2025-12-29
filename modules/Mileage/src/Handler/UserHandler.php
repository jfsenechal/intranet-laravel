<?php

namespace AcMarche\Mileage\Handler;

use AcMarche\Mileage\Models\PersonalInformation;
use App\Models\User;
use Exception;

final class UserHandler
{
    /**
     * @throws Exception
     */
    public static function createPersonalInformation(User $user, array $data): void
    {
        try {
            $existing = PersonalInformation::where('username', $user->username)->first();

            if ($existing) {
                $existing->update([
                    'omnium' => $data['omnium'] ?? false,
                    'college_trip_date' => $data['college_trip_date'] ?? null,
                ]);

                return;
            }

            // Create PersonalInformation with data from the form
            PersonalInformation::create([
                'username' => $user->username,
                'omnium' => $data['omnium'] ?? false,
                'college_trip_date' => $data['college_trip_date'] ?? null,
            ]);
        } catch (Exception $e) {
            throw new Exception("Error creating PersonalInformation for user {$user->username}: {$e->getMessage()}");
        }
    }
}
