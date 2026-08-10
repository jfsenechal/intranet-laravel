<?php

declare(strict_types=1);

use AcMarche\Hrm\Models\Deadline;
use AcMarche\Hrm\Models\Employee;

it('wraps content that does not start with a block element', function (string $stored, string $expected): void {
    $deadline = Deadline::factory()->create(['note' => $stored]);

    $this->artisan('hrm:wrap-rich-text')->assertSuccessful();

    expect($deadline->refresh()->note)->toBe($expected);
})->with([
    'inline html' => ['Ligne 1<br>Ligne 2', '<p>Ligne 1<br>Ligne 2</p>'],
    'inline formatting tag' => ['<strong>Important</strong> à relire', '<p><strong>Important</strong> à relire</p>'],
    'plain text' => ['Bonjour', '<p>Bonjour</p>'],
]);

it('leaves content already starting with a block element untouched', function (string $stored): void {
    $deadline = Deadline::factory()->create(['note' => $stored]);

    $this->artisan('hrm:wrap-rich-text')->assertSuccessful();

    expect($deadline->refresh()->note)->toBe($stored);
})->with([
    'paragraph' => ['<p>Déjà du <strong>HTML</strong></p>'],
    'bullet list' => ['<ul><li>Un</li><li>Deux</li></ul>'],
    'heading' => ['<h2>Titre</h2><p>Suite</p>'],
]);

it('leaves null and empty columns untouched', function (?string $stored): void {
    $deadline = Deadline::factory()->create(['note' => $stored]);

    $this->artisan('hrm:wrap-rich-text')->assertSuccessful();

    expect($deadline->refresh()->note)->toBe($stored);
})->with([
    'null' => [null],
    'empty string' => [''],
]);

it('covers rich text columns on other tables', function (): void {
    $employee = Employee::factory()->create(['notes' => 'Ligne 1<br>Ligne 2']);

    $this->artisan('hrm:wrap-rich-text')
        ->expectsOutputToContain('employees.notes: 1 row(s)')
        ->assertSuccessful();

    expect($employee->refresh()->notes)->toBe('<p>Ligne 1<br>Ligne 2</p>');
});

it('reports the rows to update without writing them on a dry run', function (): void {
    $deadline = Deadline::factory()->create(['note' => 'Ligne 1<br>Ligne 2']);

    $this->artisan('hrm:wrap-rich-text', ['--dry-run' => true])
        ->expectsOutputToContain('1 row(s) would be wrapped')
        ->assertSuccessful();

    expect($deadline->refresh()->note)->toBe('Ligne 1<br>Ligne 2');
});

it('reports when there is nothing to wrap', function (): void {
    Deadline::factory()->create(['note' => '<p>Déjà correct</p>']);

    $this->artisan('hrm:wrap-rich-text')
        ->expectsOutputToContain('No RichEditor content to wrap.')
        ->assertSuccessful();
});
