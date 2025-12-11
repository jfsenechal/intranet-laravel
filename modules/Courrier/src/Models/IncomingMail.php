<?php

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\IncomingMailFactory;
use AcMarche\Security\Models\HasUserAdd;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class IncomingMail extends Model
{
    use HasFactory;
    use HasUserAdd;
    use SoftDeletes;

    protected $connection = 'maria-courrier';

    protected $fillable = [
        'reference',
        'sender_name',
        'sender_address',
        'received_date',
        'subject',
        'description',
        'status',
        'attachment_path',
        'attachment_name',
        'attachment_size',
        'attachment_mime',
        'assigned_to',
        'processed_date',
        'notes',
        'user_add',
    ];

    protected static function booted(): void
    {
        self::bootHasUser();
    }

    protected static function newFactory(): IncomingMailFactory
    {
        return IncomingMailFactory::new();
    }

    protected function casts(): array
    {
        return [
            'received_date' => 'date',
            'processed_date' => 'date',
            'attachment_size' => 'integer',
        ];
    }
}
