<?php

declare(strict_types=1);

use AcMarche\Conseil\Service\AgendaRemoteSender;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    config()->set('conseil.remote.base_url', 'https://remote.example.test/api/');
    Storage::fake('public');
});

it('uploads the agenda file to the remote server', function (): void {
    Http::fake([
        'remote.example.test/api/*' => Http::response('{}'),
    ]);
    Storage::disk('public')->put('conseil/agendas/oj.pdf', 'pdf-content');

    new AgendaRemoteSender()->send('conseil/agendas/oj.pdf');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://remote.example.test/api/ordre/add.php'
        && $request->isMultipart()
        && collect($request->data())->contains(
            fn (array $part): bool => $part['name'] === 'file_name' && $part['contents'] === 'oj.pdf',
        ));
});

it('throws when the agenda file is missing', function (): void {
    Http::fake();

    expect(fn () => new AgendaRemoteSender()->send('conseil/agendas/missing.pdf'))
        ->toThrow(RuntimeException::class);

    Http::assertNothingSent();
});

it('throws when the remote server returns an error', function (): void {
    Http::fake([
        'remote.example.test/api/*' => Http::response('{"error":"Fichier refusé"}'),
    ]);
    Storage::disk('public')->put('conseil/agendas/oj.pdf', 'pdf-content');

    expect(fn () => new AgendaRemoteSender()->send('conseil/agendas/oj.pdf'))
        ->toThrow(RuntimeException::class, 'Fichier refusé');
});

it('deletes the agenda file on the remote server', function (): void {
    Http::fake([
        'remote.example.test/api/*' => Http::response('{}'),
    ]);

    new AgendaRemoteSender()->delete('conseil/agendas/oj.pdf');

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://remote.example.test/api/ordre/delete.php'
        && $request['file_name'] === 'oj.pdf');
});
