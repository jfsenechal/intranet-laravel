<?php

use AcMarche\Courrier\Models\IncomingMail;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

RefreshDatabaseState::$migrated = true;
uses(DatabaseTruncation::class);

test('example', function () {
    dump(config(['database.connections']));
    $response = $this->get('/');
    $mail = IncomingMail::factory()->create([
        'reference_number' => 'TEST-2024-001',
        'sender' => 'Test Sender',
        'description' => 'Test Description',
    ]);
    $mail = User::factory()->create([

    ]);
    $response->assertStatus(301);
});
