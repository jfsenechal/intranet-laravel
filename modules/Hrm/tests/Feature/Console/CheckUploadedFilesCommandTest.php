<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\HrDocument;
use Illuminate\Support\Facades\Storage;

it('succeeds when every referenced file exists', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $document = HrDocument::factory()->create(['file_name' => 'hrm/documents/present.pdf']);
    Storage::disk('local')->put($document->file_name, 'content');

    $this->artisan('hrm:check-uploads')
        ->expectsOutputToContain('All referenced files exist.')
        ->assertSuccessful();
});

it('fails and lists a missing referenced file', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    HrDocument::factory()->create(['file_name' => 'hrm/documents/gone.pdf']);

    $this->artisan('hrm:check-uploads')
        ->expectsOutputToContain('referenced file(s) are missing')
        ->assertFailed();
});

it('checks files stored on the public disk', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    Employee::factory()->create(['photo' => 'hrm/photos/missing.jpg']);

    $this->artisan('hrm:check-uploads')
        ->expectsOutputToContain('referenced file(s) are missing')
        ->assertFailed();
});

it('ignores records without a referenced file', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    Employee::factory()->create(['photo' => null, 'candidate_file_name' => null]);

    $this->artisan('hrm:check-uploads')
        ->expectsOutputToContain('All referenced files exist.')
        ->assertSuccessful();
});
