<?php

declare(strict_types=1);

use AcMarche\App\Sms\SmsUtils;

uses(PHPUnit\Framework\TestCase::class);

test('cleanPhoneNumber normalises various belgian formats', function (string $input): void {
    expect(SmsUtils::cleanPhoneNumber($input))->toBe('32476662615');
})->with([
    '+32476662615',
    '32476662615',
    '0476662615',
    '320476662615',
    '+320476662615',
    '0476 66 26 15',
    '(+32)476/66.26.15',
]);

test('cleanPhoneNumber keeps non-belgian international numbers', function (): void {
    expect(SmsUtils::cleanPhoneNumber('+212661745985'))->toBe('32212661745985')
        ->and(SmsUtils::cleanPhoneNumber('212661745985'))->toBe('32212661745985');
});
