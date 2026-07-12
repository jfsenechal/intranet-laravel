<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Contract;
use AcMarche\Hrm\Models\Evaluation;
use AcMarche\Hrm\Models\HrDocument;
use Illuminate\Support\Facades\Storage;

it('moves a legacy file to the canonical path referenced by the database', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $contract = Contract::factory()->create(['file1_name' => 'hrm/contracts/abc.pdf']);
    Storage::disk('local')->put('hrm/contrats/abc.pdf', 'content');

    $this->artisan('hrm:relocate-uploads')
        ->expectsOutputToContain('1 file(s) moved from a legacy folder.')
        ->assertSuccessful();

    Storage::disk('local')->assertExists($contract->file1_name);
    Storage::disk('local')->assertMissing('hrm/contrats/abc.pdf');
});

it('leaves files that are already in place untouched', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $document = HrDocument::factory()->create(['file_name' => 'hrm/documents/present.pdf']);
    Storage::disk('local')->put($document->file_name, 'content');

    $this->artisan('hrm:relocate-uploads')
        ->expectsOutputToContain('1 file(s) already in place.')
        ->expectsOutputToContain('0 file(s) moved from a legacy folder.')
        ->assertSuccessful();
});

it('does not move anything during a dry run', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    Contract::factory()->create(['file1_name' => 'hrm/contracts/abc.pdf']);
    Storage::disk('local')->put('hrm/contrats/abc.pdf', 'content');

    $this->artisan('hrm:relocate-uploads', ['--dry-run' => true])
        ->expectsOutputToContain('1 file(s) would be moved from a legacy folder.')
        ->assertSuccessful();

    Storage::disk('local')->assertExists('hrm/contrats/abc.pdf');
    Storage::disk('local')->assertMissing('hrm/contracts/abc.pdf');
});

it('reports files that cannot be located anywhere', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    Contract::factory()->create(['file1_name' => 'hrm/contracts/gone.pdf']);

    $this->artisan('hrm:relocate-uploads')
        ->expectsOutputToContain('could not be located anywhere')
        ->assertFailed();
});

it('breaks a tie toward the legacy folder when a basename exists twice', function (): void {
    Storage::fake('local');
    Storage::fake('public');

    $evaluation = Evaluation::factory()->create(['file1_name' => 'hrm/evaluations/dup.pdf']);
    Storage::disk('local')->put('hrm/valorizations/dup.pdf', 'legacy-evaluation');
    Storage::disk('local')->put('hrm/formations/dup.pdf', 'unrelated-training');

    $this->artisan('hrm:relocate-uploads')
        ->expectsOutputToContain('1 file(s) moved from a legacy folder.')
        ->assertSuccessful();

    Storage::disk('local')->assertExists($evaluation->file1_name);
    expect(Storage::disk('local')->get($evaluation->file1_name))->toBe('legacy-evaluation');
    Storage::disk('local')->assertExists('hrm/formations/dup.pdf');
});
