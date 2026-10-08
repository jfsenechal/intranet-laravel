<?php

declare(strict_types=1);

use AcMarche\News\Enums\DepartmentEnum;
use AcMarche\News\Enums\RolesEnum;
use AcMarche\News\Models\News;
use AcMarche\Security\Models\Role;
use Illuminate\Support\Facades\Mail;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Mail::fake();
});

it('shows only the news of the user department and common news', function (): void {
    auth()->user()->addRole(Role::query()->firstOrCreate(['name' => RolesEnum::ROLE_NEWS_VILLE->value]));
    $common = News::factory()->create(['name' => 'Nouvelle commune', 'department' => DepartmentEnum::COMMON->value]);
    $ville = News::factory()->create(['name' => 'Nouvelle ville', 'department' => DepartmentEnum::VILLE->value]);
    $cpas = News::factory()->create(['name' => 'Nouvelle cpas', 'department' => DepartmentEnum::CPAS->value]);
    $cpas->updateQuietly(['user_add' => 'other-user']);

    livewire('home.latest-news')
        ->assertOk()
        ->assertSee($common->name)
        ->assertSee($ville->name)
        ->assertDontSee($cpas->name);
});

it('shows the author own news of another department', function (): void {
    $user = auth()->user();
    $user->addRole(Role::query()->firstOrCreate(['name' => RolesEnum::ROLE_NEWS_VILLE->value]));
    $own = News::factory()->create(['name' => 'Ma nouvelle cpas', 'department' => DepartmentEnum::CPAS->value]);
    $own->updateQuietly(['user_add' => $user->username]);

    livewire('home.latest-news')
        ->assertOk()
        ->assertSee($own->name);
});
