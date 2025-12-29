<?php

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class IncomingMailService extends Pivot
{
    public $timestamps = false;

    protected $table = 'incoming_mail_service';

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
