---
paths:
  - 'modules/Pst/database/migrations/**'
---

# Migrations

## Never add schema to the initial Pst create migration
`0001_01_01_000003_create_pst_table.php` returns early on `Schema::hasTable('strategic_objectives')`. The pst database predates this app, so that guard is always true in production and the whole migration is skipped there — anything added to it only ever exists on fresh installs.

Symptom: "Base table or view not found" at runtime for a table the migration clearly creates. It bit `services`, then `media` and `histories`, all of which kept their legacy unprefixed names in production while the models pointed at `pst_*`.

Always add a new, separately-timestamped migration instead, guarded on both table names so it is a no-op on fresh installs (see `2026_08_14_120000_rename_media_and_histories_to_pst_prefixed_tables.php`). Tests cannot catch this: they run the whole chain against a fresh in-memory sqlite schema, so verify against the real pst database.
