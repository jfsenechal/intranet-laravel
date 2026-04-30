<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Facades\Filament;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('pst'));
    $this->user = User::factory()->create();
});

test('an unauthenticated user cannot access the pst panel', function (): void {
    auth()->logout();

    $this->get('/pst')
        ->assertRedirect();
});

test('an authenticated user can access the pst panel', function (): void {
    $this->actingAs($this->user)
        ->get('/pst')
        ->assertOk();
});
