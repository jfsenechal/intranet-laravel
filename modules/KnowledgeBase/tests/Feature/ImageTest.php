<?php

declare(strict_types=1);

use AcMarche\KnowledgeBase\Documentation\ImageStore;
use AcMarche\KnowledgeBase\Tests\KnowledgeBaseUsers;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

const KB_IMAGE = '3f1c2b4a-1234-4abc-8def-0123456789ab.png';

beforeEach(function (): void {
    Storage::fake('knowledge-base');
    $this->disk = Storage::disk('knowledge-base');
    $this->disk->put('images/'.KB_IMAGE, UploadedFile::fake()->image('a.png')->getContent());
    $this->disk->put('vpn-forticlient.md', '# VPN');
});

it('stores an uploaded image privately under a random name', function (): void {
    $images = app(ImageStore::class);

    $filename = $images->store(UploadedFile::fake()->image('schema.png'));

    expect($images->isValidFilename($filename))->toBeTrue()
        ->and($this->disk->exists('images/'.$filename))->toBeTrue()
        ->and($images->url($filename))->toBe('/knowledge-base/images/'.$filename);
});

it('refuses to store a file that is not a raster image', function (string $name, string $content): void {
    $upload = UploadedFile::fake()->createWithContent($name, $content);

    expect(fn () => app(ImageStore::class)->store($upload))->toThrow(ValidationException::class)
        ->and($this->disk->files('images'))->toBe(['images/'.KB_IMAGE]);
})->with([
    'svg' => ['x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
    'pdf' => ['x.pdf', "%PDF-1.4\n%âãÏÓ\n"],
]);

it('serves an image only to users who can read the knowledge base', function (string $profile, int $status): void {
    $user = KnowledgeBaseUsers::make($profile);

    if ($user !== null) {
        $this->actingAs($user);
    }

    $this->get(route('knowledge-base.image', ['filename' => KB_IMAGE]))->assertStatus($status);
})->with([
    'guest' => ['guest', 403],
    'user without role' => ['no role', 403],
    'reader' => ['reader', 200],
    'intranet administrator' => ['administrator', 200],
]);

it('marks a served image as privately cacheable', function (): void {
    $this->actingAs(KnowledgeBaseUsers::make('reader'));

    $response = $this->get(route('knowledge-base.image', ['filename' => KB_IMAGE]));

    expect($response->headers->get('Cache-Control'))->toContain('private')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});

it('returns not found for a missing image or a path outside the images folder', function (string $path): void {
    $this->actingAs(KnowledgeBaseUsers::make('reader'));

    $this->get('/knowledge-base/images/'.$path)->assertNotFound();
})->with([
    'missing image' => ['00000000-0000-4000-8000-000000000000.png'],
    'markdown page' => ['vpn-forticlient.md'],
    'encoded traversal' => ['..%2Fvpn-forticlient.md'],
]);
