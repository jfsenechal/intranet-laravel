<?php

declare(strict_types=1);

use AcMarche\Ad\Ai\ClassifiedAdModerationAgent;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\CreateClassifiedAd;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\EditClassifiedAd;
use AcMarche\Ad\Models\Category;
use AcMarche\Ad\Models\ClassifiedAd;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Prompts\AgentPrompt;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Mail::fake();
    Storage::fake('public');
    Filament::setCurrentPanel(Filament::getPanel('ad-panel'));
    auth()->user()->update(['is_administrator' => true]);
    $this->category = Category::factory()->create();
});

function submitAd(Category $category, string $name = 'Vélo à vendre'): Livewire\Features\SupportTesting\Testable
{
    return livewire(CreateClassifiedAd::class)
        ->fillForm([
            'name' => $name,
            'content' => '<p>Vélo de ville en bon état.</p>',
            'category_id' => $category->id,
            'end_date' => now()->addWeek()->toDateString(),
            'medias' => [UploadedFile::fake()->image('velo.jpg')],
        ])
        ->call('create');
}

it('publishes an ad the moderation accepts and sends it its text and images', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => true, 'reasons' => []]]);

    submitAd($this->category)->assertHasNoFormErrors();

    assertDatabaseHas(ClassifiedAd::class, ['name' => 'Vélo à vendre']);
    ClassifiedAdModerationAgent::assertPrompted(fn (AgentPrompt $prompt): bool => $prompt->contains('Vélo de ville en bon état.')
        && count($prompt->attachments) === 1);
});

it('refuses an ad the moderation rejects and tells the author why', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => false, 'reasons' => ['Le titre contient une insulte.']]]);

    submitAd($this->category, 'Titre insultant')
        ->assertNotified('Annonce refusée : elle ne respecte pas les règles de bonne conduite');

    assertDatabaseMissing(ClassifiedAd::class, ['name' => 'Titre insultant']);
    Mail::assertNothingSent();
});

it('still publishes the ad when the moderation is unavailable', function (): void {
    ClassifiedAdModerationAgent::fake(fn (): never => throw new RuntimeException('Provider down'));

    submitAd($this->category)->assertHasNoFormErrors();

    assertDatabaseHas(ClassifiedAd::class, ['name' => 'Vélo à vendre']);
});

it('saves an edited ad the moderation accepts', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => true, 'reasons' => []]]);
    $classifiedAd = ClassifiedAd::factory()->create(['category_id' => $this->category->id]);

    livewire(EditClassifiedAd::class, ['record' => $classifiedAd->id])
        ->fillForm([
            'name' => 'Vélo vendu',
            'medias' => [UploadedFile::fake()->image('velo.jpg')],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($classifiedAd->refresh()->name)->toBe('Vélo vendu');
});

it('refuses an edit the moderation rejects and keeps the previous version', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => false, 'reasons' => ['Le texte contient une insulte.']]]);
    $classifiedAd = ClassifiedAd::factory()->create(['name' => 'Vélo à vendre', 'category_id' => $this->category->id]);

    livewire(EditClassifiedAd::class, ['record' => $classifiedAd->id])
        ->fillForm([
            'name' => 'Titre insultant',
            'medias' => [UploadedFile::fake()->image('velo.jpg')],
        ])
        ->call('save')
        ->assertNotified('Annonce refusée : elle ne respecte pas les règles de bonne conduite');

    expect($classifiedAd->refresh()->name)->toBe('Vélo à vendre');
});

it('skips the check when only the category or end date change', function (): void {
    ClassifiedAdModerationAgent::fake();
    Storage::disk('public')->put('ad/velo.jpg', 'image');
    $classifiedAd = ClassifiedAd::factory()->create([
        'category_id' => $this->category->id,
        'content' => '<p>Vélo de ville en bon état.</p><ul><li>Taille M</li></ul>',
        'medias' => ['ad/velo.jpg'],
    ]);
    $otherCategory = Category::factory()->create();

    livewire(EditClassifiedAd::class, ['record' => $classifiedAd->id])
        ->fillForm([
            'category_id' => $otherCategory->id,
            'end_date' => now()->addMonth()->toDateString(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($classifiedAd->refresh()->category_id)->toBe($otherCategory->id);
    ClassifiedAdModerationAgent::assertNeverPrompted();
});

it('publishes an ad without attachments', function (): void {
    ClassifiedAdModerationAgent::fake([['compliant' => true, 'reasons' => []]]);

    livewire(CreateClassifiedAd::class)
        ->fillForm([
            'name' => 'Covoiturage Marche - Namur',
            'content' => '<p>Je cherche un covoiturage le matin.</p>',
            'category_id' => $this->category->id,
            'end_date' => now()->addWeek()->toDateString(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    assertDatabaseHas(ClassifiedAd::class, ['name' => 'Covoiturage Marche - Namur']);
    ClassifiedAdModerationAgent::assertPrompted(fn (AgentPrompt $prompt): bool => count($prompt->attachments) === 0);
});
