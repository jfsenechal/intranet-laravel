<?php

declare(strict_types=1);

use AcMarche\Mileage\Models\BudgetArticle;

test('the display name joins the codes and the name', function (): void {
    $budgetArticle = BudgetArticle::factory()->make([
        'functional_code' => '104/123',
        'economic_code' => '48',
        'name' => 'Frais de déplacement',
    ]);

    expect($budgetArticle->display_name)->toBe('104/123 - 48 Frais de déplacement');
});

test('the display name drops the codes that are missing', function (): void {
    $budgetArticle = BudgetArticle::factory()->make([
        'functional_code' => '104/123',
        'economic_code' => null,
        'name' => 'Frais de déplacement',
    ]);

    expect($budgetArticle->display_name)->toBe('104/123 Frais de déplacement');

    $budgetArticle = BudgetArticle::factory()->make([
        'functional_code' => null,
        'economic_code' => null,
        'name' => 'Frais de déplacement',
    ]);

    expect($budgetArticle->display_name)->toBe('Frais de déplacement');
});

test('displayNameOptions labels the options with the display name', function (): void {
    $budgetArticle = BudgetArticle::factory()->create([
        'functional_code' => '104/123',
        'economic_code' => '48',
        'name' => 'Frais de déplacement',
    ]);

    expect(BudgetArticle::displayNameOptions())
        ->toBe(['Frais de déplacement' => '104/123 - 48 Frais de déplacement'])
        ->and(BudgetArticle::displayNameOptions('id'))
        ->toBe([$budgetArticle->id => '104/123 - 48 Frais de déplacement']);
});
