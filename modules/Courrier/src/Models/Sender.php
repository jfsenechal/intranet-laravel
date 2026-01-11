<?php

namespace AcMarche\Courrier\Models;

use AcMarche\Courrier\Database\Factories\SenderFactory;
use AcMarche\Courrier\Models\Concerns\HasDepartmentScope;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[UseFactory(SenderFactory::class)]
final class Sender extends Model
{
    use HasDepartmentScope;
    use HasFactory;

    public $timestamps = false;

    protected $connection = 'maria-courrier';

    protected $fillable = [
        'slug',
        'name',
        'department',
    ];

    public function incomingMails(): BelongsToMany
    {
        return $this->belongsToMany(IncomingMail::class, 'incoming_mail_service')
            ->withPivot('is_primary');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(Recipient::class, 'recipient_service');
    }

    protected static function booted(): void
    {
        self::creating(function (Service $service): void {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    protected static function newFactory(): ServiceFactory
    {
        return ServiceFactory::new();
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
