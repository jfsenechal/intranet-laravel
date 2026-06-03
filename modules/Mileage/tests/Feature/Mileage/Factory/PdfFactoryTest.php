<?php

declare(strict_types=1);

use AcMarche\Mileage\Factory\PdfFactory;
use AcMarche\Mileage\Models\Declaration;
use AcMarche\Mileage\Models\Trip;
use Spatie\LaravelPdf\Facades\Pdf;

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
