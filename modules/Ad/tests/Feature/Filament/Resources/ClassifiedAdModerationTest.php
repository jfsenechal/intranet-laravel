<?php

declare(strict_types=1);

use AcMarche\Ad\Ai\ClassifiedAdModerationAgent;
use AcMarche\Ad\Filament\Resources\ClassifiedAd\Pages\CreateClassifiedAd;
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
