<?php

declare(strict_types=1);

it('stores the module uploads at the disk root without the uploads prefix', function (string $key, string $expected): void {
    expect(config($key))->toBe($expected);
})->with([
    'news medias' => ['news.uploads.medias', 'news'],
    'aldermen agenda files' => ['aldermen-agenda.uploads.files', 'aldermen-agenda'],
    'offense files' => ['offenses.storage.directory', 'offense'],
]);
