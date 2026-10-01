<?php

declare(strict_types=1);

use AcMarche\Agent\Models\Profile;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Security\Ldap\UserLdap;
use LdapRecord\Laravel\Testing\DirectoryEmulator;

beforeEach(function (): void {
    DirectoryEmulator::setup('default');
});

afterEach(function (): void {
    DirectoryEmulator::tearDown();
});

describe('username linking from agent profiles', function (): void {
    it('fills the username of an employee linked by a profile', function (): void {
        $employee = Employee::factory()->create(['username' => null]);
        Profile::factory()->create(['employee_id' => $employee->id, 'username' => 'amartin']);

        $this->artisan('hrm:sync-employees')->assertSuccessful();

        expect($employee->refresh()->username)->toBe('amartin');
    });

    it('overwrites a username that diverges from the profile', function (): void {
        $employee = Employee::factory()->create(['username' => 'numerotation']);
        Profile::factory()->create(['employee_id' => $employee->id, 'username' => 'aleboutte']);

        $this->artisan('hrm:sync-employees')->assertSuccessful();

        expect($employee->refresh()->username)->toBe('aleboutte');
    });

    it('ignores a profile without an employee_id', function (): void {
        $employee = Employee::factory()->create(['username' => null]);
        Profile::factory()->create(['employee_id' => null, 'username' => 'orphan']);

        $this->artisan('hrm:sync-employees')->assertSuccessful();

        expect($employee->refresh()->username)->toBeNull();
    });

    it('ignores a profile without a username', function (): void {
        $employee = Employee::factory()->create(['username' => null]);
        Profile::factory()->create(['employee_id' => $employee->id, 'username' => null]);

        $this->artisan('hrm:sync-employees')->assertSuccessful();

        expect($employee->refresh()->username)->toBeNull();
    });

    it('leaves the username of an employee no profile points at', function (): void {
        $employee = Employee::factory()->create(['username' => 'legacy']);

        $this->artisan('hrm:sync-employees')->assertSuccessful();

        expect($employee->refresh()->username)->toBe('legacy');
    });
});

it('syncs the ldap contact details of an employee linked in the same run', function (): void {
    $userLdap = new UserLdap;
    $userLdap->cn = 'Alice Martin';
    $userLdap->samaccountname = 'amartin';
    $userLdap->mail = 'alice.martin@marche.be';
    $userLdap->mobile = '0470123456';
    $userLdap->telephonenumber = '084321100';
    $userLdap->ipphone = '1234';
    $userLdap->save();

    $employee = Employee::factory()->create(['username' => null]);
    Profile::factory()->create(['employee_id' => $employee->id, 'username' => 'amartin']);

    $this->artisan('hrm:sync-employees')->assertSuccessful();

    expect($employee->refresh())
        ->username->toBe('amartin')
        ->professional_email->toBe('alice.martin@marche.be')
        ->professional_mobile->toBe('0470123456')
        ->professional_phone->toBe('084321100')
        ->professional_phone_extension->toBe('1234');
});

it('clears the contact details of an employee with no ldap account', function (): void {
    $employee = Employee::factory()->create([
        'username' => null,
        'professional_email' => 'stale@marche.be',
        'professional_mobile' => '0470000000',
        'professional_phone' => '084000000',
        'professional_phone_extension' => '9999',
    ]);
    Profile::factory()->create(['employee_id' => $employee->id, 'username' => 'unknown']);

    $this->artisan('hrm:sync-employees')->assertSuccessful();

    expect($employee->refresh())
        ->username->toBe('unknown')
        ->professional_email->toBeNull()
        ->professional_mobile->toBeNull()
        ->professional_phone->toBeNull()
        ->professional_phone_extension->toBeNull();
});
