<?php

declare(strict_types=1);

use AcMarche\Document\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('lets a guest read a document without authentication', function (): void {
    $document = Document::factory()->create(['name' => 'Un document public']);

    auth()->logout();

    $this->assertGuest();

    $this->get(route('document.show', $document))
        ->assertOk()
        ->assertSee('Un document public');
});

it('shows a download link when the file exists on disk', function (): void {
    Storage::fake(config('document.storage.disk'));
    Storage::disk(config('document.storage.disk'))
        ->putFileAs('documents', UploadedFile::fake()->create('rapport.pdf'), 'rapport.pdf');

    $document = Document::factory()->create(['file_name' => 'rapport.pdf']);

    auth()->logout();

    $this->get(route('document.show', $document))
        ->assertOk()
        ->assertSee('Télécharger le document');
});

it('says so when the document holds no file', function (): void {
    Storage::fake(config('document.storage.disk'));

    $document = Document::factory()->create(['file_name' => '']);

    auth()->logout();

    $this->get(route('document.show', $document))
        ->assertOk()
        ->assertSee("Aucun fichier n'est associé à ce document.", escape: false);
});

it('returns 404 for a missing document', function (): void {
    auth()->logout();

    $this->get(route('document.show', 999999))
        ->assertNotFound();
});
