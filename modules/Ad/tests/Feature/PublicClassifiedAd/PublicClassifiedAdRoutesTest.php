<?php

declare(strict_types=1);

use AcMarche\Ad\Ai\ClassifiedAdModerationAgent;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\ClassifiedAdResource;
use AcMarche\Ad\Http\Controllers\PublicClassifiedAdController;
use AcMarche\Ad\Models\Category;
use AcMarche\Ad\Models\ClassifiedAd;
use AcMarche\Hrm\Enums\StatusEnum;
use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\withSession;

beforeEach(function (): void {
    Mail::fake();
    Storage::fake('public');
    auth()->logout();
    $this->category = Category::factory()->create();
});

/**
 * An "Agent" in the HR database, under an active contract unless told otherwise.
 *
 * @param  array<string, mixed>  $attributes
 */
function hrEmployee(array $attributes = [], bool $hasActiveContract = true): Employee
{
    $employee = Employee::factory()->create([
        'first_name' => 'Diane',
        'last_name' => 'Petit',
        'professional_email' => 'diane@marche.be',
        'status' => StatusEnum::AGENT->value,
        ...$attributes,
    ]);
    Contract::factory()->create([
        'employee_id' => $employee->id,
        'is_closed' => ! $hasActiveContract,
        'is_suspended' => false,
        'end_date' => null,
    ]);

    return $employee;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function publicAdPayload(Category $category, array $overrides = []): array
{
    return [
        'name' => 'Vélo à vendre',
        'content' => "Vélo de ville\nen bon état.",
        'category_id' => $category->id,
        'end_date' => now()->addWeek()->toDateString(),
        ...$overrides,
    ];
}

it('sends an authenticated user to the panel form', function (): void {
    $this->actingAs(App\Models\User::factory()->create());

    get(route('ad.public.create'))->assertRedirect(ClassifiedAdResource::getUrl('create', panel: 'ad-panel'));
});

it('asks a guest for their email first', function (): void {
    get(route('ad.public.create'))
        ->assertOk()
        ->assertSeeText('Identifiez-vous')
        ->assertDontSeeText("Publier l'annonce");
});

it('lets an eligible employee email through to the ad form', function (): void {
    hrEmployee();

    post(route('ad.public.identify'), ['email' => '  Diane@Marche.be '])
        ->assertRedirect(route('ad.public.create'))
        ->assertSessionHas(PublicClassifiedAdController::SESSION_KEY, 'diane@marche.be');

    get(route('ad.public.create'))
        ->assertSeeText("Publier l'annonce")
        ->assertSeeText('Petit Diane');
});

it('accepts the private email of an eligible employee', function (): void {
    hrEmployee(['professional_email' => null, 'private_email' => 'diane@gmail.com']);

    post(route('ad.public.identify'), ['email' => 'diane@gmail.com'])
        ->assertSessionHas(PublicClassifiedAdController::SESSION_KEY, 'diane@gmail.com');
});

it('refuses an employee without an active contract', function (): void {
    hrEmployee(hasActiveContract: false);

    post(route('ad.public.identify'), ['email' => 'diane@marche.be'])
        ->assertSessionHas('error')
        ->assertSessionMissing(PublicClassifiedAdController::SESSION_KEY);
});

it('refuses an email unknown to the HR database', function (): void {
    post(route('ad.public.identify'), ['email' => 'nobody@example.com'])
        ->assertSessionHas('error')
        ->assertSessionMissing(PublicClassifiedAdController::SESSION_KEY);
});

it('publishes an ad for an identified employee', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => true, 'reasons' => []]]);
    hrEmployee();

    withSession([PublicClassifiedAdController::SESSION_KEY => 'diane@marche.be'])
        ->post(route('ad.public.store'), publicAdPayload($this->category, [
            'content' => "<b>Vélo</b>\nen bon état.",
            'medias' => [UploadedFile::fake()->image('velo.jpg')],
        ]))
        ->assertRedirect(route('ad.public.create'))
        ->assertSessionHas('success');

    $classifiedAd = ClassifiedAd::query()->where('name', 'Vélo à vendre')->sole();
    expect($classifiedAd->user_add)->toBe('diane@marche.be')
        ->and($classifiedAd->content)->toBe('<p>&lt;b&gt;Vélo&lt;/b&gt;<br />'."\n".'en bon état.</p>')
        ->and($classifiedAd->medias)->toHaveCount(1);
    Storage::disk('public')->assertExists($classifiedAd->medias[0]);
});

it('refuses to publish without an identified employee', function (): void {
    post(route('ad.public.store'), publicAdPayload($this->category))
        ->assertRedirect(route('ad.public.create'))
        ->assertSessionHas('error');

    assertDatabaseMissing(ClassifiedAd::class, ['name' => 'Vélo à vendre']);
});

it('refuses to publish once the session email is no longer eligible', function (): void {
    withSession([PublicClassifiedAdController::SESSION_KEY => 'gone@marche.be'])
        ->post(route('ad.public.store'), publicAdPayload($this->category))
        ->assertRedirect(route('ad.public.create'))
        ->assertSessionMissing(PublicClassifiedAdController::SESSION_KEY);

    assertDatabaseMissing(ClassifiedAd::class, ['name' => 'Vélo à vendre']);
});

it('refuses an ad the moderation rejects and removes its attachments', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => false, 'reasons' => ['Le titre contient une insulte.']]]);
    hrEmployee();

    withSession([PublicClassifiedAdController::SESSION_KEY => 'diane@marche.be'])
        ->post(route('ad.public.store'), publicAdPayload($this->category, [
            'name' => 'Titre insultant',
            'medias' => [UploadedFile::fake()->image('velo.jpg')],
        ]))
        ->assertSessionHas('moderation_reasons', ['Le titre contient une insulte.']);

    assertDatabaseMissing(ClassifiedAd::class, ['name' => 'Titre insultant']);
    expect(Storage::disk('public')->allFiles())->toBeEmpty();
    Mail::assertNothingSent();
});

it('validates the ad fields', function (): void {
    hrEmployee();

    withSession([PublicClassifiedAdController::SESSION_KEY => 'diane@marche.be'])
        ->post(route('ad.public.store'), publicAdPayload($this->category, [
            'name' => '',
            'category_id' => 999999,
            'end_date' => now()->subDay()->toDateString(),
        ]))
        ->assertSessionHasErrors(['name', 'category_id', 'end_date']);
});
