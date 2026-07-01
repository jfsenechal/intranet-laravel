<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Security\Models\Role;
use App\Models\User;

it('shows the incoming mail create form with preview on the left', function (): void {
    $user = User::factory()->create([
        'email' => 'courrier-demo@pestphp.com',
        'password' => 'password',
    ]);
    $role = Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN->value]);
    $user->roles()->attach($role);

    $this->actingAs($user);

    visit(route('filament.courrier-panel.resources.incoming-mails.create'))
        ->assertSee('Aperçu')
        ->assertSee('Informations du courrier')
        ->screenshot(true, 'incoming-mail-create-form');
});
