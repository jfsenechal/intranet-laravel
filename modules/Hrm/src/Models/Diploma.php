<?php

namespace AcMarche\Hrm\Models;

use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Diploma extends Model
{
    use HasUserAdd;

    protected $connection = 'maria-hrm';

    protected $table = 'diplomas';

    protected $fillable = [
        'employee_id',
        'title',
        'certificate_file',
        'user_add',
        'updated_by',
    ];

    /**
     * @return BelongsTo<Employee>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted(): void
    {
        self::bootHasUser();
    }
}
