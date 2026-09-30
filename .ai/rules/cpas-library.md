---
paths:
  - 'modules/CpasLibrary/**'
---

# Cpas Library

## Production CpasLibrary tables are legacy Doctrine schemas
In production, the `maria-cpas-library` tables were renamed from the Symfony app (for example `categorie` → `categories`), so the column defaults in the `create` migrations never ran there. Example: `categories.public` is NOT NULL with no default, while tests (fresh migration) give it `default(false)`. Any NOT NULL column that a form doesn't set needs a default on the model (`$attributes`) or a `creating` hook, and a test that asserts the model attribute rather than the database default.
