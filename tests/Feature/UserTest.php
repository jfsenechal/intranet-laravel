<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

//RefreshDatabaseState::$migrated = true;

test('example', function () {
    $response = $this->get('/');
    $user = User::factory()->create([
        'email' => 'titi@marche.be',
    ]);
    $response->assertStatus(301);
    expect($user)
        ->toBeInstanceOf(User::class)
        ->and($user->email)->toBe('titi@marche.be');
});
