<?php

declare(strict_types=1);

use AcMarche\Hrm\Filament\Exports\AbsenceExport;
use AcMarche\Hrm\Filament\Exports\EmployeeExport;
use AcMarche\Hrm\Filament\Exports\ProcessExport;
use AcMarche\Hrm\Filament\Exports\TrainingExport;
use AcMarche\Hrm\Models\Absence;
use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\Process;
use AcMarche\Hrm\Models\Training;

it('filters absence headings to the selected columns in declared order', function (): void {
    $export = new AbsenceExport(Absence::query(), ['agent', 'reason']);

    expect($export->headings())->toBe(['Agent', 'Raison']);
});

it('keeps the declared column order regardless of selection order', function (): void {
    $export = new AbsenceExport(Absence::query(), ['reason', 'agent']);

    expect($export->headings())->toBe(['Agent', 'Raison']);
});

it('returns all process headings when no columns are selected', function (): void {
    $export = new ProcessExport(Process::query());

    expect($export->headings())->toBe(array_values(ProcessExport::columns()));
});

it('filters employee headings to the selected columns', function (): void {
    $export = new EmployeeExport(Employee::query(), ['last_name', 'private_email']);

    expect($export->headings())->toBe(['Nom', 'Email']);
});

it('returns all training headings when selection is empty', function (): void {
    $export = new TrainingExport(Training::query());

    expect($export->headings())->toBe(array_values(TrainingExport::columns()));
});

it('ignores unknown column keys', function (): void {
    $export = new TrainingExport(Training::query(), ['name', 'does_not_exist']);

    expect($export->headings())->toBe(['Intitulé']);
});
