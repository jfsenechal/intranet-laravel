<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Filament\Resources\HrDocuments;

use AcMarche\Hrm\Models\Employee;
use AcMarche\Hrm\Models\HrDocument;
use Illuminate\Support\Facades\Storage;

final class HrDocumentCreator
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function createForEmployee(Employee $employee, array $data): HrDocument
    {
        $path = $data['file_name'] ?? null;

        return $employee->documents()->create([
            'name' => $data['name'],
            'file_name' => $path,
            'mime' => $path ? (Storage::disk('public')->mimeType($path) ?: '') : '',
            'notes' => $data['notes'] ?? null,
        ]);
    }
}
