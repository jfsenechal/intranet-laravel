<?php

declare(strict_types=1);

use AcMarche\QrCode\Enums\QrCodeActionEnum;
use AcMarche\QrCode\Models\QrCode;
use AcMarche\QrCode\Service\QrCodeGenerator;

it('builds a URL payload', function (): void {
    $qrCode = new QrCode();
    $qrCode->action = QrCodeActionEnum::URL;
    $qrCode->message = 'https://acmarche.be';

    $payload = (new QrCodeGenerator())->buildPayload($qrCode);

    expect($payload)->toBe('https://acmarche.be');
});

it('builds an SMS payload', function (): void {
    $qrCode = new QrCode();
    $qrCode->action = QrCodeActionEnum::SMS;
    $qrCode->phone_number = '+32474000000';
    $qrCode->message = 'Hello';

    expect((new QrCodeGenerator())->buildPayload($qrCode))
        ->toBe('SMSTO:+32474000000:Hello');
});

it('builds a wifi payload', function (): void {
    $qrCode = new QrCode();
    $qrCode->action = QrCodeActionEnum::WIFI;
    $qrCode->ssid = 'home';
    $qrCode->password = 'secret;1';
    $qrCode->encryption = 'WPA';
    $qrCode->network_hidden = false;

    expect((new QrCodeGenerator())->buildPayload($qrCode))
        ->toBe('WIFI:T:WPA;S:home;P:secret\\;1;H:false;;');
});

it('builds an EPC payload', function (): void {
    $qrCode = new QrCode();
    $qrCode->action = QrCodeActionEnum::EPC;
    $qrCode->recipient = 'AC Marche';
    $qrCode->iban = 'BE68 5390 0754 7034';
    $qrCode->amount = '12.5';
    $qrCode->message = 'Cotisation';

    $payload = (new QrCodeGenerator())->buildPayload($qrCode);

    expect($payload)
        ->toContain('BCD')
        ->toContain('AC Marche')
        ->toContain('BE68539007547034')
        ->toContain('EUR12.50');
});

it('renders an SVG QR code', function (): void {
    $qrCode = new QrCode();
    $qrCode->action = QrCodeActionEnum::URL;
    $qrCode->message = 'https://acmarche.be';
    $qrCode->format = 'SVG';

    $svg = (new QrCodeGenerator())->render($qrCode);

    expect($svg)->toStartWith('<?xml')->toContain('<svg');
});
