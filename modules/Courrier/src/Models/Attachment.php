<?php

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Attachment extends Model
{
    public const UPDATED_AT = null;

    protected $connection = 'maria-courrier';

    protected $fillable = [
        'incoming_mail_id',
        'file_name',
        'mime',
    ];

    public function incomingMail(): BelongsTo
    {
        return $this->belongsTo(IncomingMail::class);
    }
}
