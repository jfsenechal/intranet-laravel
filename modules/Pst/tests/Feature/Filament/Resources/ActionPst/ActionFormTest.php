<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature\Filament\Resources\ActionPst;

use AcMarche\Pst\Enums\RolesEnum;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\CreateActionPst;
use AcMarche\Pst\Filament\Resources\ActionPst\Pages\EditActionPst;
use AcMarche\Pst\Models\Action;
use AcMarche\Pst\Models\OperationalObjective;
use AcMarche\Pst\Models\Service;
use AcMarche\Pst\Models\StrategicObjective;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class ActionFormTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;

    private User $responsibleUser;

    private User $regularUser;

    private Role $adminRole;

    private Action $action;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('pst'));

        $this->adminRole = Role::factory()->create(['name' => RolesEnum::ADMIN->value]);

        $this->adminUser = User::factory()->create();
        $this->adminUser->roles()->attach($this->adminRole);

        $this->responsibleUser = User::factory()->create();

        $this->regularUser = User::factory()->create();

        $this->action = $this->createAction();
    }

    public function test_admin_can_see_validated_field_on_create(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateActionPst::class)
            ->assertFormFieldVisible('validated');
    }

    public function test_regular_user_cannot_see_validated_field_on_create(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(CreateActionPst::class)
            ->assertFormFieldHidden('validated');
    }

    public function test_admin_can_see_roadmap_field_on_create(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(CreateActionPst::class)
            ->assertFormFieldVisible('roadmap');
    }

    public function test_regular_user_cannot_see_roadmap_field_on_create(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(CreateActionPst::class)
            ->assertFormFieldHidden('roadmap');
    }

    public function test_name_field_is_not_readonly_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldEnabled('name');
    }

    /**
     * Note: name field uses readOnly() not disabled(). Filament's readOnly()
     * keeps the field enabled but prevents editing in the UI.
     * There's no assertFormFieldReadOnly() method in Filament testing.
     * This test verifies the field exists and is accessible on edit.
     */
    public function test_name_field_exists_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldExists('name');
    }

    public function test_operational_objective_is_enabled_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldEnabled('operational_objective_id');
    }

    public function test_operational_objective_is_disabled_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldDisabled('operational_objective_id');
    }

    public function test_type_field_is_enabled_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldEnabled('type');
    }

    public function test_type_field_is_disabled_for_regular_user_on_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldDisabled('type');
    }

    public function test_team_step_is_visible_for_admin_on_edit(): void
    {
        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldVisible('action_mandatory');
    }

    public function test_team_step_is_visible_for_responsible_in_leader_service_on_edit(): void
    {
        $service = Service::factory()->create();
        $service->users()->attach($this->responsibleUser);
        $this->action->leaderServices()->attach($service);

        $this->actingAs($this->responsibleUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertFormFieldVisible('action_mandatory');
    }

    public function test_all_fields_are_enabled_on_create_for_regular_user(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(CreateActionPst::class)
            ->assertFormFieldEnabled('name')
            ->assertFormFieldEnabled('type');
    }

    // ActionPolicy tests - isUserLinkedToAction
    public function test_regular_user_not_linked_to_action_cannot_edit(): void
    {
        $this->actingAs($this->regularUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertForbidden();
    }

    public function test_mandataire_cannot_edit_action_even_if_directly_linked(): void
    {
        $mandataireRole = Role::factory()->create(['name' => RolesEnum::MANDATAIRE->value]);
        $mandataireUser = User::factory()->create();
        $mandataireUser->roles()->attach($mandataireRole);

        // Link mandataire to action
        $this->action->users()->attach($mandataireUser);

        $this->actingAs($mandataireUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertForbidden();
    }

    public function test_mandataire_cannot_edit_action_even_if_in_leader_service(): void
    {
        $mandataireRole = Role::factory()->create(['name' => RolesEnum::MANDATAIRE->value]);
        $mandataireUser = User::factory()->create();
        $mandataireUser->roles()->attach($mandataireRole);

        // Link mandataire via leader service
        $service = Service::factory()->create();
        $service->users()->attach($mandataireUser);
        $this->action->leaderServices()->attach($service);

        $this->actingAs($mandataireUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertForbidden();
    }

    public function test_user_directly_linked_to_action_can_edit(): void
    {
        $this->action->users()->attach($this->regularUser);

        $this->actingAs($this->regularUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertOk();
    }

    public function test_user_in_leader_service_can_edit(): void
    {
        $service = Service::factory()->create();
        $service->users()->attach($this->regularUser);
        $this->action->leaderServices()->attach($service);

        $this->actingAs($this->regularUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertOk();
    }

    public function test_admin_can_edit_any_action(): void
    {
        // Admin is not linked to action but should still be able to edit
        $this->actingAs($this->adminUser);

        Livewire::test(EditActionPst::class, ['record' => $this->action->id])
            ->assertOk();
    }

    private function createAction(): Action
    {
        $strategicObjective = StrategicObjective::factory()->create();
        $operationalObjective = OperationalObjective::factory()->create([
            'strategic_objective_id' => $strategicObjective->id,
        ]);

        return Action::factory()->create([
            'operational_objective_id' => $operationalObjective->id,
        ]);
    }
}
