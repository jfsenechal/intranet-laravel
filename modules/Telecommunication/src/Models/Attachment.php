<?php

declare(strict_types=1);

namespace AcMarche\Telecommunication\Models;

use AcMarche\Telecommunication\Database\Factories\AttachmentFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UseFactory(AttachmentFactory::class)]
#[Connection('maria-telecommunication')]
#[Fillable([
    'telephone_id',
    'file_name',
])]
final class Attachment extends Model
{
    use HasFactory;

    /**
     * @return BelongsTo<Telephone, $this>
     */
    public function telephone(): BelongsTo
    {
        return $this->belongsTo(Telephone::class);
    }
}
