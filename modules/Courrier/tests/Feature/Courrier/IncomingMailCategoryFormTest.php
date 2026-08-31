<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\CreateIncomingMail;
use AcMarche\Courrier\Filament\Resources\IncomingMails\Pages\EditIncomingMail;
use AcMarche\Courrier\Models\Category;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;

use function Pest\Livewire\livewire;

/**
 * Act as a user administering a single department, which is what the
 * create/edit form expects.
 */
function actAsCourrierAdmin(RolesEnum $role): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::factory()->create(['name' => $role->value]));

    test()->actingAs($user);

    return $user;
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    $this->category = Category::factory()->create(['name' => 'Facture']);
});

it('stores the category chosen on the create form', function (): void {
    actAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    livewire(CreateIncomingMail::class)
        ->assertFormFieldExists('category_id')
        ->fillForm([
            'reference_number' => '4321',
            'mail_date' => now()->format('Y-m-d'),
            'sender' => 'Avec catégorie SA',
            'description' => 'Avec catégorie',
            'category_id' => $this->category->id,
            'attachment_file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(IncomingMail::where('sender', 'Avec catégorie SA')->value('category_id'))
        ->toBe($this->category->id);
});

it('leaves the category empty when none is chosen', function (): void {
    actAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    livewire(CreateIncomingMail::class)
        ->fillForm([
            'reference_number' => '4322',
            'mail_date' => now()->format('Y-m-d'),
            'sender' => 'Sans catégorie SA',
            'attachment_file' => UploadedFile::fake()->create('test.pdf', 100, 'application/pdf'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(IncomingMail::where('sender', 'Sans catégorie SA')->value('category_id'))->toBeNull();
});

it('fills the edit form with the current category and saves a new one', function (): void {
    actAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);
    $mail = IncomingMail::factory()->create(['category_id' => $this->category->id]);
    $other = Category::factory()->create(['name' => 'Facture rectificative']);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertFormSet(['category_id' => $this->category->id])
        ->fillForm(['category_id' => $other->id])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($mail->fresh()->category_id)->toBe($other->id);
});

it('hides the category field from an administrator of another department', function (): void {
    actAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN);

    livewire(CreateIncomingMail::class)
        ->assertFormFieldHidden('category_id');
});

it('keeps the category of a mail edited by an administrator of another department', function (): void {
    actAsCourrierAdmin(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN);
    $mail = IncomingMail::factory()->create(['category_id' => $this->category->id]);

    livewire(EditIncomingMail::class, ['record' => $mail->id])
        ->assertFormFieldHidden('category_id')
        ->fillForm(['description' => 'Modifiée sans toucher à la catégorie'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($mail->fresh()->category_id)->toBe($this->category->id);
});
