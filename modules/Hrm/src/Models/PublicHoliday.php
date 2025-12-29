<?php

namespace AcMarche\Hrm\Models;

use Illuminate\Database\Eloquent\Model;

final class PublicHoliday extends Model
{
    public $timestamps = false;

    protected $connection = 'maria-hrm';

    protected $table = 'public_holidays';

    protected $fillable = [
        'title',
        'holiday_date',
    ];

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }
}
