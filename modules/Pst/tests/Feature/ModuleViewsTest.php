<?php

declare(strict_types=1);

namespace AcMarche\Pst\Tests\Feature;

use AcMarche\Pst\Filament\Components\ProgressEntry;
use AcMarche\Pst\Filament\Resources\ActionPst\Tables\Columns\ProgressColumn;

/**
 * Module views are registered under the pst namespace by ModuleServiceProviderTrait,
 * so every reference to one has to carry the pst:: prefix.
 */
it('resolves the views referenced by the module', function (string $view): void {
    expect(view()->exists($view))->toBeTrue();
})->with([
    'pst::components.progress-entry',
    'pst::components.btn_add',
    'pst::tables.columns.progress-column',
    'pst::pdf.action',
    'pst::filament.resources.strategic-objective-list',
]);

it('points its filament components at namespaced views', function (object $component): void {
    $view = (fn (): string => $this->view)->call($component);

    expect($view)->toStartWith('pst::')
        ->and(view()->exists($view))->toBeTrue();
})->with([
    fn () => ProgressEntry::make('state_percentage'),
    fn () => ProgressColumn::make('state_percentage'),
]);
