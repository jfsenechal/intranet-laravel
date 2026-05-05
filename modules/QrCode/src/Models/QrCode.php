<?php

declare(strict_types=1);

namespace AcMarche\QrCode\Models;

use AcMarche\QrCode\Database\Factories\QrCodeFactory;
use AcMarche\QrCode\Enums\QrCodeActionEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[UseFactory(QrCodeFactory::class)]
#[Fillable([
    'username',
    'name',
    'action',
    'color',
    'background_color',
    'pixels',
    'format',
    'style',
    'margin',
    'label_text',
    'label_color',
    'label_size',
    'label_alignment',
    'file_path',
    'message',
    'phone_number',
    'email',
    'subject',
    'iban',
    'amount',
    'recipient',
    'latitude',
    'longitude',
    'ssid',
    'password',
    'encryption',
    'network_hidden',
])]
#[Table(name: 'qr_codes')]
final class QrCode extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * @return BelongsTo<User, QrCode>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }

    protected function casts(): array
    {
        return [
            'action' => QrCodeActionEnum::class,
            'pixels' => 'integer',
            'margin' => 'integer',
            'label_size' => 'integer',
            'network_hidden' => 'boolean',
        ];
    }
}
