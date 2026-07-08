<?php

declare(strict_types=1);

use App\Filament\Resources\FailedJobs\FailedJobResource;
use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Filament\Resources\Jobs\JobResource;
use App\Filament\Resources\Jobs\Pages\ListJobs;
use App\Models\FailedJob;
use App\Models\Job;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('admin-panel');
});

function insertPendingJob(): void
{
    Job::query()->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Mail\\SendQueuedMailable']),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => now()->timestamp,
        'created_at' => now()->timestamp,
    ]);
}

function insertFailedJob(): FailedJob
{
    return FailedJob::query()->create([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode([
            'uuid' => (string) Str::uuid(),
            'displayName' => 'App\\Mail\\SendQueuedMailable',
            'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
            'data' => ['commandName' => 'Foo', 'command' => 'serialized'],
        ]),
        'exception' => "RuntimeException: SMTP connection failed\n#0 /app/foo.php(1)",
        'failed_at' => now(),
    ]);
}

it('lets an administrator see pending jobs', function (): void {
    $admin = User::factory()->create(['is_administrator' => true]);
    insertPendingJob();

    $this->actingAs($admin);

    livewire(ListJobs::class)
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords(Job::all());
});

it('lets an administrator see failed jobs with their error', function (): void {
    $admin = User::factory()->create(['is_administrator' => true]);
    $failed = insertFailedJob();

    $this->actingAs($admin);

    livewire(ListFailedJobs::class)
        ->assertOk()
        ->loadTable()
        ->assertCanSeeTableRecords([$failed])
        ->assertTableColumnStateSet('exception', $failed->exceptionSummary(), $failed);
});

it('restricts the resources to administrators', function (): void {
    $admin = User::factory()->create(['is_administrator' => true]);
    $regular = User::factory()->create(['is_administrator' => false]);

    $this->actingAs($regular);
    expect(JobResource::canAccess())->toBeFalse()
        ->and(FailedJobResource::canAccess())->toBeFalse();

    $this->actingAs($admin);
    expect(JobResource::canAccess())->toBeTrue()
        ->and(FailedJobResource::canAccess())->toBeTrue();
});

it('requeues a failed job through the retry action', function (): void {
    $admin = User::factory()->create(['is_administrator' => true]);
    $failed = insertFailedJob();

    Artisan::shouldReceive('call')
        ->once()
        ->with('queue:retry', ['id' => [$failed->uuid]])
        ->andReturn(0);

    $this->actingAs($admin);

    livewire(ListFailedJobs::class)
        ->callAction(TestAction::make('retry')->table($failed))
        ->assertNotified();
});
