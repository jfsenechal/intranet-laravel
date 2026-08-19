<?php

declare(strict_types=1);

use AcMarche\Courrier\Enums\DepartmentCourrierEnum;
use AcMarche\Courrier\Enums\RolesEnum;
use AcMarche\Courrier\Filament\Pages\CategorizeIncomingMails;
use AcMarche\Courrier\Models\Category;
use AcMarche\Courrier\Models\IncomingMail;
use AcMarche\Courrier\Repository\IncomingMailRepository;
use AcMarche\Security\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

/**
 * Act as a courrier administrator of the given department.
 */
function actAsCategorizer(RolesEnum $role): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::factory()->create(['name' => $role->value]));

    test()->actingAs($user);

    return $user;
}

/**
 * A CPAS mail dated after the category was introduced, which is what the page
 * lists.
 */
function cpasMail(array $attributes = []): IncomingMail
{
    return IncomingMail::factory()->create([
        'department' => DepartmentCourrierEnum::CPAS->value,
        'mail_date' => IncomingMailRepository::CATEGORY_START_DATE,
        ...$attributes,
    ]);
}

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('courrier-panel'));

    $this->category = Category::factory()->create(['name' => 'Facture']);
});

it('lists only the cpas mail that has no category', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    $withoutCategory = cpasMail();
    $withCategory = cpasMail(['category_id' => $this->category->id]);

    livewire(CategorizeIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$withoutCategory])
        ->assertCanNotSeeTableRecords([$withCategory]);
});

it('ignores the mail encoded before the category existed', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    $sinceTheStart = cpasMail();
    $before = cpasMail(['mail_date' => '2026-06-30']);

    livewire(CategorizeIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$sinceTheStart])
        ->assertCanNotSeeTableRecords([$before]);

    expect(CategorizeIncomingMails::getNavigationBadge())->toBe('1');
});

it('ignores the mail of the other departments', function (): void {
    $user = actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);
    // Also allowed to read the Ville mail, so only the page's own filter keeps
    // it out of the list.
    $user->roles()->attach(Role::factory()->create(['name' => RolesEnum::ROLE_INDICATEUR_VILLE_READ->value]));

    $cpas = cpasMail();
    $ville = IncomingMail::factory()->create(['department' => DepartmentCourrierEnum::VILLE->value]);

    livewire(CategorizeIncomingMails::class)
        ->loadTable()
        ->assertCanSeeTableRecords([$cpas])
        ->assertCanNotSeeTableRecords([$ville]);
});

it('sets the category straight from the row', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    $mail = cpasMail();

    livewire(CategorizeIncomingMails::class)
        ->loadTable()
        ->call('updateTableColumnState', 'category_id', (string) $mail->id, $this->category->id);

    expect($mail->fresh()->category_id)->toBe($this->category->id);
});

it('sets the category of several mails at once', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    $mails = collect([cpasMail(), cpasMail()]);

    livewire(CategorizeIncomingMails::class)
        ->loadTable()
        ->callTableBulkAction('setCategory', $mails, ['category_id' => $this->category->id])
        ->assertHasNoActionErrors()
        ->assertNotified();

    expect($mails->every(fn (IncomingMail $mail): bool => $mail->fresh()->category_id === $this->category->id))
        ->toBeTrue();
});

it('is closed to an administrator of another department', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_VILLE_ADMIN);

    expect(CategorizeIncomingMails::canAccess())->toBeFalse();

    $this->get(CategorizeIncomingMails::getUrl(panel: 'courrier-panel'))->assertForbidden();
});

it('is open to a cpas administrator', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    expect(CategorizeIncomingMails::canAccess())->toBeTrue();

    $this->get(CategorizeIncomingMails::getUrl(panel: 'courrier-panel'))->assertOk();
});

it('badges the navigation with the number of mails left to classify', function (): void {
    actAsCategorizer(RolesEnum::ROLE_INDICATEUR_CPAS_ADMIN);

    expect(CategorizeIncomingMails::getNavigationBadge())->toBeNull();

    cpasMail();
    cpasMail();
    cpasMail(['category_id' => $this->category->id]);

    expect(CategorizeIncomingMails::getNavigationBadge())->toBe('2');
});
