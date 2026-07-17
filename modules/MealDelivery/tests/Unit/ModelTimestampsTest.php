<?php

declare(strict_types=1);

use AcMarche\MealDelivery\Models\Absence;
use AcMarche\MealDelivery\Models\Client;
use AcMarche\MealDelivery\Models\DeliveryRoute;
use AcMarche\MealDelivery\Models\Diet;
use AcMarche\MealDelivery\Models\Meal;
use AcMarche\MealDelivery\Models\Menu;
use AcMarche\MealDelivery\Models\Note;
use AcMarche\MealDelivery\Models\Order;
use AcMarche\MealDelivery\Models\Week;

/**
 * The legacy `cpas_repas` tables were renamed into place rather than built by the
 * `create` path of the module migrations, so most of them have no `created_at` /
 * `updated_at` columns. A model that lets Eloquent manage timestamps against one of
 * those tables fails on insert with "Unknown column 'updated_at'".
 */
it('does not manage timestamps on tables without timestamp columns', function (): void {
    foreach ([Absence::class, DeliveryRoute::class, Diet::class, Meal::class, Menu::class, Note::class, Week::class] as $model) {
        expect((new $model)->usesTimestamps())->toBeFalse("{$model} must not manage timestamps");
    }
});

it('manages timestamps on tables with timestamp columns', function (): void {
    foreach ([Client::class, Order::class] as $model) {
        expect((new $model)->usesTimestamps())->toBeTrue("{$model} must manage timestamps");
    }
});
