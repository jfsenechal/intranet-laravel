<?php

declare(strict_types=1);

namespace AcMarche\Hrm\Models;

use AcMarche\Hrm\Database\Factories\JobFunctionFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

#[Connection('maria-hrm')]
#[Fillable([
    'name',
])]
#[Table(name: 'job_functions')]
#[UseFactory(JobFunctionFactory::class)]
final class JobFunction extends Model
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    /**
     * @return HasMany<Application>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }
}
