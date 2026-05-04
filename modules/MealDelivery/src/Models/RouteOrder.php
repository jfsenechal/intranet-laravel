<?php

declare(strict_types=1);

namespace AcMarche\MealDelivery\Models;

use Illuminate\Database\Eloquent\Attributes\Connection;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Connection('maria-meal-delivery')]
#[Fillable(['route_id', 'client_id', 'position'])]
final class RouteOrder extends Model
{
    protected $table = 'route_orders';

    /**
     * @return BelongsTo<DeliveryRoute, RouteOrder>
     */
    public function deliveryRoute(): BelongsTo
    {
        return $this->belongsTo(DeliveryRoute::class, 'route_id');
    }

    /**
     * @return BelongsTo<Client, RouteOrder>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    protected function casts(): array
    {
        return [
            'position' => 'integer',
        ];
    }
}
