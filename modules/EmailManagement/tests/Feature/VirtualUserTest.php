<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Models\Citoyen;
use AcMarche\EmailManagement\Models\VirtualUser;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('hides the password hashes when serialized', function (): void {
    $virtualUser = VirtualUser::factory()->create(['userPassword' => '{SSHA}secret']);

    expect($virtualUser->refresh()->toArray())
        ->not->toHaveKeys(['userPassword', 'legacy_password']);
});

it('links a citoyen to its login by mail address', function (): void {
    $virtualUser = VirtualUser::factory()->create(['username' => 'alice.martin@marche.be']);
    $citoyen = Citoyen::factory()->create(['mail' => 'alice.martin@marche.be']);
    VirtualUser::factory()->create();

    expect($citoyen->virtualUser->is($virtualUser))->toBeTrue();
});

it('refuses a login without a username', function (): void {
    expect(fn () => VirtualUser::factory()->create(['username' => null]))
        ->toThrow(QueryException::class);
});

/**
 * Asserted on the definition because the in-memory test connection runs with foreign_keys
 * off, so sqlite stores the key without enforcing it.
 */
it('links citoyens.mail to virtual_users.username with a foreign key', function (): void {
    $foreignKeys = collect(Schema::connection('maria-email-management')->getForeignKeys('citoyens'));

    expect($foreignKeys->firstWhere('columns', ['mail']))
        ->foreign_table->toBe('virtual_users')
        ->foreign_columns->toBe(['username'])
        ->on_update->toBe('cascade')
        ->on_delete->toBe('restrict');
});

it('no longer has the hands table', function (): void {
    expect(Schema::connection('maria-email-management')->hasTable('hands'))->toBeFalse();
});

it('refuses to drop a hands table that still holds a password', function (): void {
    $schema = Schema::connection('maria-email-management');
    $migration = require __DIR__.'/../../database/migrations/2026_11_05_000002_drop_hands_table.php';
    $migration->down();
    DB::connection('maria-email-management')->table('hands')->insert([
        'uid' => 'alice.martin',
        'email' => 'alice.martin@marche.be',
        'password' => '{SSHA}original',
    ]);

    expect(fn () => $migration->up())->toThrow(RuntimeException::class)
        ->and($schema->hasTable('hands'))->toBeTrue();
});

it('no longer stores the deprecated columns on citoyens', function (): void {
    expect(Schema::connection('maria-email-management')->getColumnListing('citoyens'))
        ->not->toContain('userPassword')
        ->not->toContain('legacy_password')
        ->not->toContain('gosaMailQuota')
        ->not->toContain('cn')
        ->not->toContain('dn')
        ->not->toContain('gosaMailForwardingAddress')
        ->not->toContain('uid')
        ->not->toContain('gosaMailAlternateAddress')
        ->not->toContain('password_changed_at');
});

it('stores the personal data of a citoyen encrypted', function (): void {
    $citoyen = Citoyen::factory()->create([
        'employeeNumber' => '62011108917',
        'postalAddress' => 'Rue du Parc 1',
        'l' => 'Marche-en-Famenne',
        'recovery_email' => 'jean@example.be',
        'recovery_phone' => '0470123456',
    ]);

    $stored = DB::connection('maria-email-management')->table('citoyens')->where('id', $citoyen->id)->sole();

    expect($stored)
        ->employeeNumber->not->toContain('62011108917')
        ->postalAddress->not->toContain('Rue du Parc')
        ->l->not->toContain('Marche')
        ->recovery_email->not->toContain('jean@example.be')
        ->recovery_phone->not->toContain('0470123456')
        ->and($citoyen->refresh())
        ->employeeNumber->toBe('62011108917')
        ->postalAddress->toBe('Rue du Parc 1')
        ->l->toBe('Marche-en-Famenne')
        ->recovery_email->toBe('jean@example.be')
        ->recovery_phone->toBe('0470123456');
});
