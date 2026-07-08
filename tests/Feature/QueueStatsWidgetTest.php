<?php

declare(strict_types=1);

use App\Filament\Widgets\QueueStatsWidget;
use App\Models\FailedJob;
use App\Models\Job;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('admin-panel');
});

function seedQueueRows(int $pending, int $failed): void
{
    for ($i = 0; $i < $pending; $i++) {
        Job::query()->insert([
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Mail\\SendQueuedMailable']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => now()->timestamp,
            'created_at' => now()->timestamp,
        ]);
    }

    for ($i = 0; $i < $failed; $i++) {
        FailedJob::query()->create([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'App\\Mail\\SendQueuedMailable']),
            'exception' => 'RuntimeException: boom',
            'failed_at' => now(),
        ]);
    }
}

it('renders the pending and failed counts for administrators', function (): void {
    $admin = User::factory()->create(['is_administrator' => true]);
    seedQueueRows(pending: 3, failed: 2);

    $this->actingAs($admin);

    livewire(QueueStatsWidget::class)
        ->assertOk()
        ->assertSee('Jobs en attente')
        ->assertSee('Jobs échoués')
        ->assertSee('3')
        ->assertSee('2');
});

it('is hidden from non-administrators', function (): void {
    $this->actingAs(User::factory()->create(['is_administrator' => false]));
    expect(QueueStatsWidget::canView())->toBeFalse();

    $this->actingAs(User::factory()->create(['is_administrator' => true]));
    expect(QueueStatsWidget::canView())->toBeTrue();
});
