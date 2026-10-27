<?php

declare(strict_types=1);

use AcMarche\EmailManagement\Sieve\VacationScript;

it('builds a script the sieve interpreter will accept', function (): void {
    $script = VacationScript::build('Je suis absent', "A partir du 1er.\n\nMerci.", 3, ['alice@ac.marche.be']);

    expect($script)
        ->toContain('require ["vacation"];')
        ->toContain(':days 3')
        ->toContain(':addresses ["alice@ac.marche.be"]')
        ->toContain(':subject "Je suis absent"')
        ->toContain("text:\nA partir du 1er.\n\nMerci.\n.\n;");
});

it('omits the addresses clause when none are known', function (): void {
    expect(VacationScript::build('Absent', 'Message'))->not->toContain(':addresses');
});

it('never emits fewer than one day', function (): void {
    expect(VacationScript::build('Absent', 'Message', 0))->toContain(':days 1');
});

it('escapes quotes and backslashes in the subject', function (): void {
    $script = VacationScript::build('Absent "vraiment" \\ ici', 'Message');

    expect($script)->toContain(':subject "Absent \\"vraiment\\" \\\\ ici"');
});

it('flattens a multi-line subject, which a quoted string cannot hold', function (): void {
    $script = VacationScript::build("Absent\njusqu'au 5", 'Message');

    expect($script)->toContain(':subject "Absent jusqu\'au 5"');
});

it('stuffs a message line starting with a dot so it cannot end the block early', function (): void {
    $script = VacationScript::build('Absent', ".\nsuite");

    expect($script)->toContain("text:\n..\nsuite\n.\n;");
});

it('normalises windows line endings in the message', function (): void {
    $script = VacationScript::build('Absent', "une\r\ndeux");

    expect($script)->toContain("text:\nune\ndeux\n.\n;")
        ->and($script)->not->toContain("\r");
});

it('reads back the values of a script it built', function (): void {
    $script = VacationScript::build('Absent "vraiment" \\ ici', ".point\nA partir du 1er.\n\nMerci.", 3, ['alice@ac.marche.be']);

    expect(VacationScript::parse($script))->toBe([
        'subject' => 'Absent "vraiment" \\ ici',
        'message' => ".point\nA partir du 1er.\n\nMerci.",
        'days' => 3,
    ]);
});

it('reads a vacation message written as a quoted string', function (): void {
    $script = "require \"vacation\";\nvacation :subject \"Absent\" \"Je suis \\\"absent\\\"\";\n";

    expect(VacationScript::parse($script))->toBe([
        'subject' => 'Absent',
        'message' => 'Je suis "absent"',
        'days' => 1,
    ]);
});

it('returns null for a script without a vacation action', function (): void {
    expect(VacationScript::parse("require \"fileinto\";\nfileinto \"Spam\";\n"))->toBeNull();
});
