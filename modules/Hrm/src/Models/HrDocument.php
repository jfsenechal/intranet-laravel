<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\HrDocumentFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $employee_id
 * @property string $name
 * @property string $file_name
 * @property string $mime
 * @property string|null $notes
 * @property \Carbon\CarbonImmutable $created_at
 * @property \Carbon\CarbonImmutable $updated_at
 * @property string|null $updated_by
 * @property-read Employee|null $employee
 */
#[Connection('maria-hrm')]
#[Fillable([
    'employee_id',
    'name',
    'file_name',
    'mime',
    'notes',
    'updated_by',
])]
#[Table(name: 'hr_documents')]
#[UseFactory(HrDocumentFactory::class)]
final class HrDocument extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
