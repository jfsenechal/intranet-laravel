<?php

declare(strict_types=1);

namespace AcMarche\EmailManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Hand extends Model
{
    /** @use HasFactory<\AcMarche\EmailManagement\Database\Factories\CitoyenFactory> */
    use HasFactory;

    protected $fillable = [
        'uid',
        'email',
        'password',
    ];
}
