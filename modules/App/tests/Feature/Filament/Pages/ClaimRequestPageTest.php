<?php

declare(strict_types=1);

use AcMarche\App\Filament\Pages\ClaimRequestPage;
use App\Models\User;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;

beforeEach(function (): void {
    Filament::setCurrentPanel(Filament::getPanel('app-panel'));
    $this->actingAs(User::factory()->create([
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
    ]));
});

it('renders the claim request page', function (): void {
    Livewire::test(ClaimRequestPage::class)->assertOk();
});

it('generates and streams a pdf declaration on save', function (): void {
    Pdf::fake();

    Livewire::test(ClaimRequestPage::class)
        ->fillForm([
            'last_name' => 'Dupont',
            'first_name' => 'Jean',
            'street' => 'Rue Test 1',
            'postal_code' => '6900',
            'city' => 'Marche-en-Famenne',
            'iban' => 'BE68 5390 0754 7034',
            'amount' => 1234.50,
            'filing_date' => '2026-07-09',
            'content' => 'frais de mission',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    Pdf::assertRespondedWithPdf(fn (PdfBuilder $pdf): bool => $pdf->viewName === 'app::pdf.claim-request'
        && $pdf->viewData['amount'] === '1.234,50'
        && $pdf->viewData['filing_date'] === '09-07-2026'
        && str_contains($pdf->viewData['amount_in_words'], 'mille'));
});

it('requires the mandatory fields before generating the pdf', function (): void {
    Livewire::test(ClaimRequestPage::class)
        ->fillForm([
            'last_name' => null,
            'first_name' => null,
            'amount' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'last_name' => 'required',
            'first_name' => 'required',
            'amount' => 'required',
        ]);
});
