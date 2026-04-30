<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Exports;

use AcMarche\Hrm\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class EmployeeExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Prenom',
            'Fonction',
            'Statut',
            'Entree',
            'Email',
            'Archive',
        ];
    }

    /**
     * @param  Employee  $row
     */
    public function map($row): array
    {
        return [
            $row->last_name,
            $row->first_name,
            $row->job_title,
            $row->status?->getLabel(),
            $row->hired_at?->format('d/m/Y'),
            $row->private_email,
            $row->is_archived ? 'Oui' : 'Non',
        ];
    }
}
