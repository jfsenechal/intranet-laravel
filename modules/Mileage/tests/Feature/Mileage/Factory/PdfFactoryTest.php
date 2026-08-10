<?php

declare(strict_types=1);

use AcMarche\Mileage\Calculator\DeclarationCalculator;
use AcMarche\Mileage\Enums\RolesEnum;
use AcMarche\Mileage\Factory\PdfFactory;
use AcMarche\Mileage\Models\BudgetArticle;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\PersonalInformation;
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

test('the pdf shows the up to date contact details and the account used for the declaration', function (): void {
    $declaration = Declaration::factory()->create([
        'street' => 'Vieille rue 1',
        'postal_code' => '6900',
        'city' => 'Marche',
        'iban' => 'BE68 5390 0754 7034',
    ]);
    PersonalInformation::factory()->create([
        'username' => $declaration->user_add,
        'street' => 'Nouvelle rue 2',
        'postal_code' => '6987',
        'city' => 'Rendeux',
        'iban' => 'BE62 5100 0754 7061',
    ]);

    $html = renderDeclarationPdf($declaration);

    expect($html)
        ->toContain('Nouvelle rue 2')
        ->toContain('6987 Rendeux')
        ->toContain('BE62 5100 0754 7061')
        ->toContain('Compte utilisé lors de la déclaration : BE68 5390 0754 7034')
        ->not->toContain('Vieille rue 1');
});

test('the pdf falls back to the declaration contact details when no personal information exists', function (): void {
    $declaration = Declaration::factory()->create([
        'street' => 'Vieille rue 1',
        'postal_code' => '6900',
        'city' => 'Marche',
        'iban' => 'BE68 5390 0754 7034',
    ]);

    $html = renderDeclarationPdf($declaration);

    expect($html)
        ->toContain('Vieille rue 1')
        ->toContain('6900 Marche')
        ->toContain('BE68 5390 0754 7034')
        ->not->toContain('Compte utilisé lors de la déclaration');
});

test('the pdf shows the budget article with its codes', function (): void {
    BudgetArticle::factory()->create([
        'name' => 'Frais de déplacement',
        'functional_code' => '104/123',
        'economic_code' => '48',
    ]);
    $declaration = Declaration::factory()->create(['budget_article' => 'Frais de déplacement']);

    expect(renderDeclarationPdf($declaration))->toContain('104/123 - 48 Frais de déplacement');
});
