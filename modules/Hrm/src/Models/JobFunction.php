<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class JobFunction extends Model
{
    public $timestamps = false;

    protected $connection = 'maria-hrm';

    protected $table = 'job_functions';

    protected $fillable = [
        'name',
    ];

    /**
     * @return HasMany<Application>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
