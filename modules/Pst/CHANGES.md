# Pst — session summary (2026-07-27)

Starting point: `master` at `b400669`. The Pst test suite was **9 passing / 219 errors**
out of 228. It now stands at **253 passing / 253**, 772 assertions.

The session began with a one-line request (drop `user_id` from two pivot tables) and each
fix uncovered the next problem underneath it, so the work below is a chain rather than a
set of independent items.

---

## 1. Drop `user_id` from the action pivots

**New migration** `2026_07_27_080940_remove_user_id_from_action_user_and_action_mandatory_tables.php`

`action_user` and `action_mandatory` have been keyed on `username` since the
`pst:migration` command ran, but still carried a dead `user_id` column.

- Drops the stale `*_action_id_user_id_unique` index **before** the column. Dropping the
  column first would have left MariaDB with `UNIQUE(action_id)`, silently breaking
  multi-agent actions.
- Both steps guarded (`hasColumn` / `hasIndex`), so it no-ops on databases that never had
  the column.
- `down()` re-adds a nullable `user_id` without restoring values — the old ids are not
  recoverable.

Result on the real database: both pivots now hold only `id`, `action_id`, `username`,
with `UNIQUE(action_id, username)` intact.

---

## 2. The migration chain was silently dead

Eleven follow-up migrations opened with:

```php
if (Schema::hasTable('actions')) {
    return;   // bails out precisely when the table DOES exist
}
```

`actions` is created by `0001_01_01_000003_create_pst_table.php`, which always runs first,
so **every one of these migrations was a permanent no-op**. Production never noticed
because its `maria-pst` database is a legacy one that already had the modern columns. A
freshly migrated database got stuck at the old shape — which is why tests failed with
`strategic_objectives has no column named scope`.

Each blanket guard was replaced with a column-level one expressing the real intent:

| Migration | Guard now |
|---|---|
| `add_fields_table` | skip if `actions.is_internal` or `actions.scope` exists |
| `rename_is_internal_to_scope_on_actions` / `_strategic_objectives` | run only while `is_internal` exists |
| `rename_to_validate_to_validated_on_actions` | run only while `to_validate` exists |
| `add_scope_to_operational_objectives` | skip if `scope` exists |
| `add_synergy_to_*_objectives` | skip if `synergy` exists |
| `make_synergy_default_no_and_not_null` | per-table `hasColumn('synergy')` loop |
| `remove_synergy_from_objectives_tables` | per-table `hasColumn('synergy')` loop |
| `add_soft_deletes_to_actions` | skip if `deleted_at` exists |
| `make_department_(not_)nullable_on_objectives` | guard dropped — `->change()` is idempotent |

They now run on a fresh database and stay no-ops on the legacy one.

---

## 3. Migration ordering: `department` is required

`make_department_not_nullable` and `make_department_nullable` shared the timestamp
`2026_02_02_110207`. Laravel's alphabetical tie-break put `not_nullable` **first**
(`…_no` < `…_nu`), so a fresh database ended up nullable while production was `NOT NULL`.

`make_department_not_nullable_on_objectives.php` was restamped **`110208`** so it
deterministically runs last, with a docblock explaining why the offset exists.

Applied to the real database as a no-op-equivalent: both columns already `NOT NULL`,
9 + 36 rows intact, zero nulls.

---

## 4. Cross-database user relations

`Table 'pst.users' doesn't exist` on the action view page.

`Action` and `Service` are pinned to `maria-pst`; `App\Models\User` declares no connection,
so Eloquent's `newRelatedInstance()` copies the pst connection onto it and looks `users` up
in the wrong database.

**New** `src/Models/QualifiedUsersTableTrait.php` derives the database name at runtime
instead of hardcoding `` `intranet`.`users` `` (the previous workaround, which broke the
SQLite test suite), and only qualifies when the two connections genuinely differ:

```php
protected function withQualifiedUsersTable(BelongsToMany $relation): BelongsToMany
{
    $table = $this->qualifiedUsersTable();
    $relation->getRelated()->setTable($table);      // existence subqueries
    return $relation->tap(fn ($query): mixed => $query->from($table));  // the relation itself
}
```

Both lines are needed. Setting only the relation's `from()` fixes eager/lazy loading but
**not** `whereHas()`, which builds its subquery from
`$relation->getRelated()->newQueryWithoutRelationships()` and never sees it. That gap is
what kept `ActionEditPolicyTrait::isUserLinkedToAction()` crashing after the first attempt.

The qualified name is a dotted string (`intranet.users`), not `DB::raw`, so the query
grammar wraps both segments — that is what makes the join read
`` `intranet`.`users`.`username` ``.

Applied to `Action::users()`, `Action::mandataries()`, `Service::users()`.
Verified: eager load, lazy load, `pluck`, `exists`, `whereHas` from both sides.

`Action::mandataries22()` still hardcodes `intranet` for `users`, `role_user` and `roles`.
It is unused and was left alone.

---

## 5. Module views were not namespaced

`View [components.progress-entry] not found`. `ModuleServiceProviderTrait` registers the
module's views under the `pst` namespace; three references omitted the prefix:

| File | Was | Now |
|---|---|---|
| `Filament/Components/ProgressEntry.php:13` | `components.progress-entry` | `pst::components.progress-entry` |
| `Filament/Resources/ActionPst/Tables/Columns/ProgressColumn.php:16` | `tables.columns.progress-column` | `pst::tables.columns.progress-column` |
| `Filament/Exports/PdfExport.php:23` | `pdf.action` | `pst::pdf.action` |

Only the first was in the reported trace; the other two would have failed on the actions
table and the PDF export respectively.

---

## 6. `OperationalObjective` department hook removed

A `saving()` hook nulled `department` on every `INTERNAL` objective. It was wrong on three
counts:

1. The form marks `department` **`->required()`** with a default and never hides it for
   `INTERNAL` — so the hook silently discarded valid user input.
2. `DepartmentScope` filters with `where('department', $department)`, strict equality — a
   nulled row is invisible to **every** department query.
3. With the column `NOT NULL`, saving an `INTERNAL` objective threw an integrity violation.

`StrategicObjective` has the same `scope`/`department` pair and never had such a hook, and
`isInternal()` reads `scope` alone.

Verified on the real database: an `INTERNAL` objective with `department = VILLE` now saves,
round-trips, and is found by `forDepartment('VILLE')`.

---

## 7. "Internal objectives visible to all departments" moved to `scope`

The feature was implemented as `orWhereNull('department')` in three query sites. With
`department` now `NOT NULL` that clause can never match, so it was re-expressed against the
field that actually carries the meaning:

```php
$query->forSelectedDepartment()
    ->orWhere('scope', ActionScopeEnum::INTERNAL);
```

- `Filament/Resources/ActionPst/Schemas/ActionForm.php:150`
- `Filament/Resources/OperationalObjective/Schemas/OperationalObjectiveForm.php:43`
- `Filament/Resources/ActionPst/Tables/ActionTables.php:202`

Production has 3 `INTERNAL` rows (1 strategic, 2 operational, all `VILLE`) and **zero**
null-department rows, so the old clause matched nothing — the feature was dead in
production and is now live again. This was a product decision, taken explicitly rather
than assumed.

---

## 8. Test suite repairs

**Panel id (16 files).** Tests called `Filament::getPanel('pst')`, but the panel id is
`pst-panel`. `FilamentManager::getPanel()` does not throw on an unknown id — it falls back
to the **default** panel, `app-panel`, where Pst resources are not registered. Every
`getUrl()` therefore reported `Route [filament.app-panel.resources.*] not defined`. This one
string accounted for 184 of the failures.

**Failures the panel fix unmasked** — all test-side, no app bug behind them:

- 4 badge assertions in `ActionDepartmentTest`: `toBe(5)` against Filament's
  `Tab::getBadge(): ?string`. Strict comparison could never pass → `toBe('5')`.
- `ReminderActionTest`: attached `$user->id` to a pivot keyed on `username`.
- `ReminderActionTest` ordering: `ReminderAction` reads the pivot with no `order by`, and
  SQLite serves the query from the covering `(action_id, username)` unique index, so rows
  come back **by username**. With random factory usernames the expected order was a coin
  flip. Fixed by pinning `pilot.one` / `pilot.two`; verified stable over 6 runs.
  *The production code has no `order by` either — recipient order there is DB-dependent.*
- Two helpers building `department => null` objectives now use `DepartmentEnum::CPAS`
  while the tests act as a `VILLE` user, so they genuinely exercise scope-based visibility.

**New test files**

| File | Covers |
|---|---|
| `tests/Feature/ActionPivotSchemaTest.php` | fresh-DB schema matches intent; guards the silently-skipped migrations and the `NOT NULL` department |
| `tests/Feature/CrossDatabaseUserRelationsTest.php` | the three user relations incl. the `whereHas` + policy path |
| `tests/Feature/ModuleViewsTest.php` | module views resolve and components point at `pst::` |
| `tests/Feature/OperationalObjectiveDepartmentTest.php` | department survives create/update as `INTERNAL` and is reachable via the department scope |

---

## Verification

- Pst suite: **253 / 253**, 772 assertions.
- PHPStan on `modules/Pst/src`: **202** (from 207 — no new errors introduced).
- Pint clean.
- Every schema change applied and inspected against the real `maria-pst` database; row
  counts unchanged throughout.

Caveat: the test harness shares one in-memory SQLite PDO across all connections, so the
**cross-database branch** of `QualifiedUsersTableTrait` cannot be exercised by tests. Only
the same-database branch is covered; the cross-database path was verified by hand.

---

## Corrections made during the session

Recorded because each one changed the fix:

1. Renamed the department migrations to make `NOT NULL` win, then reverted it on the
   grounds that production's `NOT NULL` was not evidence of intent — then reinstated it
   once the required-department rule was confirmed.
2. Called `orWhereNull('department')` "dead, harmless leftover". It was the *mechanism* for
   cross-department visibility, in three places. Led to item 7.
3. The first cross-database fix (`tap` + `from` only) was incomplete — it missed
   `whereHas`. Led to the `setTable` half of item 4.
4. Blamed the `ReminderActionTest` flakiness on the Select's `last_name` option ordering.
   Instrumenting it showed the real cause was the pivot's covering index.

---

## Open items

- **`Action::mandataries22()`** — unused, hardcodes `intranet` for three tables. Delete or
  port to the trait.
- **`FixCommand.php:63`** — also hardcodes `` `intranet`.`users` ``. One-off migration
  command, left as is.
- **Full-suite run** — never completed within the session (`--compact` buffers to the end).
  All changes are scoped to `modules/Pst`, and `tests/Pest.php` is unmodified, so other
  modules should be unaffected — but this is unconfirmed.
- **Migrations table** — production still holds a row for the old
  `2026_02_02_110207_make_department_not_nullable_on_objectives` name. Harmless; only
  matters on rollback.

## Side effects worth knowing

- `php artisan migrate` also applied
  `modules/Hrm/database/migrations/2026_07_27_000001_rename_code_postal_to_postal_code_on_teleworks.php`
  — an untracked, pending migration from separate in-progress work. It is now applied to
  the dev database.
- `public/css/filament/filament/app.css` was regenerated by the test runs and reverted each
  time; it is not part of this work.
