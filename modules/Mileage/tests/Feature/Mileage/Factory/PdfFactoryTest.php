<?php

declare(strict_types=1);

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Factory\PdfFactory;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use Spatie\LaravelPdf\Facades\Pdf;

function renderDeclarationPdf(Declaration $declaration): string
{
    $declaration->load('trips');

    return view('mileage::filament.export.declaration-pdf', [
        'declaration' => $declaration,
        'declarationSummary' => (new DeclarationCalculator($declaration))->calculate(),
    ])->render();
}

test('createFromDeclaration renders the declaration pdf view', function (): void {
    Pdf::fake();

    $declaration = Declaration::factory()->create();
    Trip::factory()->create([
        'declaration_id' => $declaration->id,
        'distance' => 100,
        'rate' => 0.40,
        'omnium' => 0.03,
    ]);

    (new PdfFactory())->createFromDeclaration($declaration);

    Pdf::assertViewIs('mileage::filament.export.declaration-pdf');
    Pdf::assertViewHas('declaration');
    Pdf::assertViewHas('declarationSummary');
});

test('the pdf header uses the Ville branding for a Ville declaration', function (): void {
    $declaration = Declaration::factory()->create([
        'departments' => json_encode([RolesEnum::ROLE_FINANCE_DEPLACEMENT_VILLE->value]),
    ]);

    $html = renderDeclarationPdf($declaration);

    expect($html)
        ->toContain('Administration communale')
        ->toContain('La Ville de Marche doit à :')
        ->toContain('Délibération du Collège Communal')
        ->not->toContain('C.P.A.S.');
});

test('the pdf header uses the CPAS branding for a CPAS declaration', function (): void {
    $declaration = Declaration::factory()->create([
        'departments' => json_encode([RolesEnum::ROLE_FINANCE_DEPLACEMENT_CPAS->value]),
    ]);

    $html = renderDeclarationPdf($declaration);

    expect($html)
        ->toContain('C.P.A.S.')
        ->toContain('Le C.P.A.S. doit à :')
        ->not->toContain('Administration communale')
        ->not->toContain('Délibération du Collège Communal');
});
