<?php

declare(strict_types=1);

use AcMarche\App\Enums\DepartmentEnum;
use AcMarche\News\Enums\RolesEnum as NewsRolesEnum;
use AcMarche\Pst\Enums\RolesEnum as PstRolesEnum;
use AcMarche\Security\Filament\Resources\Users\Pages\ListUsers;
use AcMarche\Security\Ldap\UserHandler;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('security-panel'));
    auth()->user()->update(['is_administrator' => true]);
});

it('filters the users holding a news or PST role of the department', function (): void {
    $newsVille = User::factory()->withRoles(NewsRolesEnum::ROLE_NEWS_VILLE->value)->create();
    $pstVille = User::factory()->withRoles(PstRolesEnum::VILLE->value)->create();
    $cpas = User::factory()->withRoles(NewsRolesEnum::ROLE_NEWS_CPAS->value, PstRolesEnum::CPAS->value)->create();

    livewire(ListUsers::class)
        ->loadTable()
        ->filterTable('department', DepartmentEnum::VILLE->value)
        ->assertCanSeeTableRecords([$newsVille, $pstVille])
        ->assertCanNotSeeTableRecords([$cpas]);
});

it('gives a new LDAP user the news role of the department of their email', function (string $email, NewsRolesEnum $role): void {
    $user = User::factory()->create(['email' => $email]);

    UserHandler::assignNewsRole($user);

    expect($user->fresh()->roles->pluck('name')->all())->toBe([$role->value]);
})->with([
    'cpas' => ['jdoe@cpas.marche.be', NewsRolesEnum::ROLE_NEWS_CPAS],
    'ville' => ['jdoe@ac.marche.be', NewsRolesEnum::ROLE_NEWS_VILLE],
    'other' => ['jdoe@example.com', NewsRolesEnum::ROLE_NEWS_VILLE],
]);
