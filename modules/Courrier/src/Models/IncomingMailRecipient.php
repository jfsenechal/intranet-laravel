<?php

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

final class IncomingMailRecipient extends Pivot
{
    protected $table = 'incoming_mail_recipient';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
