<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Valorization extends Model
{
    protected $connection = 'maria-hrm';

    protected $table = 'valorizations';

    protected $fillable = [
        'employee_id',
        'employer_name',
        'duration',
        'regime',
        'content',
        'file_name',
        'updated_by',
    ];

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
