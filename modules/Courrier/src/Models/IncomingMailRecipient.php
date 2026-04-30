<?php

declare(strict_types=1);

namespace AcMarche\Courrier\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

#[Table(name: 'incoming_mail_recipient')]
final class IncomingMailRecipient extends Pivot
{
    use HasFactory;

    #[Override]
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }
}
