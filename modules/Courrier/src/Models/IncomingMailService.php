<?php

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class IncomingMailService extends Pivot
{
    protected $table = 'incoming_mail_service';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
