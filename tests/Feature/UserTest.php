<?php

use App\Models\User;

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
