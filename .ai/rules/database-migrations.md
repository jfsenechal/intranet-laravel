---
paths:
  - 'modules/AldermenAgenda/database/migrations/**'
---

# Database Migrations

## Legacy invitation DB keeps columns the migrations think they dropped
The `maria-aldermen-agenda` (invitation) database predates this app and was renamed in place from `destinataires`/`events`, so it still carries legacy `not null` columns with no default. Every one found so far broke inserts with "SQLSTATE[HY000]: 1364 Field 'x' doesn't have a default value": `events.slugname`, then `aldermen_recipients.slugname`.

The legacy column is named `slugname` on BOTH tables — `2026_08_11_144608_drop_legacy_slug_columns` guessed `slug` for the recipients table and so dropped nothing in production. Never guess a legacy column name; confirm it against the real invitation database.

Local dev and the sqlite test schema do not reproduce this: fresh installs never create these columns, so tests pass while production fails. Always add a new, separately-timestamped migration guarded on `Schema::hasColumn()` rather than editing an already-run one, and back it with a test that re-adds the column before calling `up()` (see `tests/Feature/AldermenAgendaLegacyRecipientColumnsMigrationTest.php`).
