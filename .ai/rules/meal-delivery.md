---
paths:
  - 'modules/MealDelivery/**'
---

# Meal Delivery

## Meals with zero menus are placeholders, not deliveries
A `Meal` row exists for every day of an order's week, even when nothing is ordered (both `menus` rows sit at `quantity = 0`). The legacy Symfony app deleted those rows on save, so any logic ported from `data/CpasRepas` that means "first/last meal of the order" must filter to meals having a menu with `quantity > 0` at position 1 or 2 — a plain `MIN(date)`/`MAX(date)` over `meals` lands on a placeholder and silently breaks the DF / RF / "récipient jetable" flags on the route sheets. See `RouteSheetsAggregator::onlyDelivered()`.
