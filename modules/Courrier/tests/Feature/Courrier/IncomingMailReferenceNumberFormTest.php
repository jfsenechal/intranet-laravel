<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));
});

/**
 * Log in as a user administering the given department.
 */
function actingAsCourrierAdmin(RolesEnum $role): User
{
    $user = User::factory()->create();
    $user->addRole(Role::factory()->create(['name' => $role->value]));
    test()->actingAs($user);

    return $user;
}

describe('reference number field on the create form', function (): void {
    test('pre-fills the next reference number for a cpas admin', function (): void {
        IncomingMail::factory()->create([
            'department' => DepartmentCourrierEnum::CPAS->value,
        ]);

        actingAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        livewire(CreateIncomingMail::class)
            ->assertFormFieldExists('reference_number')
            ->assertFormSet(['reference_number' => '2']);
    });

    test('a cpas admin can submit using the pre-filled number', function (): void {
        actingAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        livewire(CreateIncomingMail::class)
            ->fillForm([
                'sender' => 'Expéditeur test',
                'mail_date' => now(),
            ])
            ->call('create')
            ->assertHasNoFormErrors(['reference_number']);
    });

    test('is required and empty for a non-cpas admin', function (): void {
        actingAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN);

        livewire(CreateIncomingMail::class)
            ->assertFormSet(['reference_number' => null])
            ->fillForm([
                'reference_number' => '',
                'sender' => 'Expéditeur test',
                'mail_date' => now(),
            ])
            ->call('create')
            ->assertHasFormErrors(['reference_number' => 'required']);
    });

    test('is required for a cpas admin when the number is cleared', function (): void {
        actingAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

        livewire(CreateIncomingMail::class)
            ->fillForm([
                'reference_number' => '',
                'sender' => 'Expéditeur test',
                'mail_date' => now(),
            ])
            ->call('create')
            ->assertHasFormErrors(['reference_number' => 'required']);
    });
});
