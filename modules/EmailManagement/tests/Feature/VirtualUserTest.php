<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Support\Facades\Schema;

it('hides the password hashes when serialized', function (): void {
    $virtualUser = VirtualUser::factory()->create(['userPassword' => '{SSHA}secret']);

    expect($virtualUser->refresh()->toArray())
        ->not->toHaveKeys(['userPassword', 'legacy_password']);
});

it('links a citoyen to its login by mail address', function (): void {
    $citoyen = Citoyen::factory()->create(['mail' => 'alice.martin@marche.be']);
    $virtualUser = VirtualUser::factory()->create(['username' => 'alice.martin@marche.be']);
    VirtualUser::factory()->create();

    expect($citoyen->virtualUser->is($virtualUser))->toBeTrue();
});

it('no longer stores the password hashes on citoyens', function (): void {
    expect(Schema::connection('maria-email-management')->getColumnListing('citoyens'))
        ->not->toContain('userPassword')
        ->not->toContain('legacy_password');
});
