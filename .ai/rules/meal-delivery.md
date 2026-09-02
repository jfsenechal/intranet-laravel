---
paths:
  - 'modules/MealDelivery/**'
---

# Meal Delivery

## Meals with zero menus are placeholders, not deliveries
A `Meal` row exists for every day of an order's week, even when nothing is ordered (both `menus` rows sit at `quantity = 0`). The legacy Symfony app deleted those rows on save, so any logic ported from `data/CpasRepas` that means "first/last meal of the order" must filter to meals having a menu with `quantity > 0` at position 1 or 2 — a plain `MIN(date)`/`MAX(date)` over `meals` lands on a placeholder and silently breaks the DF / RF / "récipient jetable" flags on the route sheets. See `RouteSheetsAggregator::onlyDelivered()`.

## Reference clients with integer(), never foreignId()
`clients.id` is a signed `int(11)` inherited from the legacy Symfony/Doctrine schema, like every column that references it (`orders`, `notes`, `client_diet`, `delivery_absences`, `guest_reservations`). `foreignId('client_id')` generates a `bigint unsigned` and the foreign key is rejected by MariaDB. Use `$table->integer('client_id')` plus an explicit `$table->foreign('client_id')->references('id')->on('clients')`.

Two things hide the failure: tests run on SQLite, where `foreignId()` passes without complaint, and the `hasTable()` idempotency guard at the top of these migrations makes a second run exit early — so a first run that created the table then died on the `ALTER TABLE` gets marked as successfully migrated, leaving a table with no FK and no unique index. After migrating a new table on MariaDB, inspect the real schema instead of trusting the migration status.

Converting `clients.id` to `bigint unsigned` was considered and rejected (2026-09-02): it buys nothing (max id 542 against a 2.1-billion limit) and would mean dropping and recreating five Doctrine-named foreign keys on a schema the legacy app still owns.
