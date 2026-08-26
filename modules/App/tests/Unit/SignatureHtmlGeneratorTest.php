<?php

declare(strict_types=1);

use AcMarche\App\Enums\SignatureEnum;
use AcMarche\App\Models\Signature;
use AcMarche\App\Services\SignatureHtmlGenerator;

function signatureWithLogo(?SignatureEnum $logo): Signature
{
    return new Signature([
        'first_name' => 'Catherine',
        'last_name' => 'Boldo',
        'address' => 'Boulevard du Midi 20',
        'postal_code' => '6900',
        'city' => 'Marche-en-Famenne',
        'email' => 'catherine.boldo@cpas.marche.be',
        'logo' => $logo,
    ]);
}

it('points the logo to the remote marche.be url', function (): void {
    $html = SignatureHtmlGenerator::generate(signatureWithLogo(SignatureEnum::CPAS));

    expect($html)
        ->toContain('src="https://www.marche.be/logo/cpas.jpg"')
        ->and($html)->not->toContain('vendor/app/images/logos');
});

it('honours a configured logo base url without a trailing slash', function (): void {
    config()->set('app.signature.logo_base_url', 'https://cdn.example.test/logos');

    $html = SignatureHtmlGenerator::generate(signatureWithLogo(SignatureEnum::ADL));

    expect($html)->toContain('src="https://cdn.example.test/logos/adl.png"');
});

it('omits the image when the signature has no logo', function (): void {
    $html = SignatureHtmlGenerator::generate(signatureWithLogo(null));

    expect($html)->not->toContain('https://www.marche.be/logo/');
});

it('renders a disclaimer link below the logo', function (): void {
    $html = SignatureHtmlGenerator::generate(signatureWithLogo(SignatureEnum::CPAS));

    expect($html)->toContain('<a href="https://www.marche.be/disclaimer/" style="font-family: \'Century Gothic\', CenturyGothic, \'Apple Gothic\', \'URW Gothic\', \'Futura\', \'Trebuchet MS\', Arial, sans-serif; color: #d4a017; text-decoration: none;">Disclaimer</a>');
});

it('renders the signature with the Century Gothic font stack', function (): void {
    $html = SignatureHtmlGenerator::generate(signatureWithLogo(SignatureEnum::CPAS));

    expect($html)
        ->toContain("font-family: 'Century Gothic', CenturyGothic, 'Apple Gothic', 'URW Gothic', 'Futura', 'Trebuchet MS', Arial, sans-serif")
        ->and($html)->not->toContain('font-family: Arial, Helvetica, sans-serif');
});
