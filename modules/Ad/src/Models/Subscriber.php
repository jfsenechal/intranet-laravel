<?php

declare(strict_types=1);

namespace AcMarche\Ad\Models;

use AcMarche\Ad\Database\Factories\SubscriberFactory;
use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Connection('maria-ad')]
#[Fillable(['email', 'first_name', 'last_name'])]
#[Table(name: 'classified_ad_subscribers')]
#[UseFactory(SubscriberFactory::class)]
final class Subscriber extends Model
{
    use HasFactory;

    public function getFullNameAttribute(): string
    {
        return $this->first_name.' '.$this->last_name;
    }
}
