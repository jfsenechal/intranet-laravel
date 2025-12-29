<?php

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class IncomingMailRecipient extends Pivot
{
    public $timestamps = false;

    protected $table = 'incoming_mail_recipient';

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
