<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class HrDocument extends Model
{
    protected $connection = 'maria-hrm';

    protected $table = 'hr_documents';

    protected $fillable = [
        'employee_id',
        'title',
        'file_name',
        'mime',
        'notes',
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
