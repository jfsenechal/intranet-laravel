# Plan — Offenses Module (Sanctions administratives)

> Filament v5 implementation plan for the Offenses module. Mirrors the existing `modules/Mediation` module conventions (PSR-4 namespace `AcMarche\Offenses\`, dedicated `maria-offenses` MariaDB connection mapped to the legacy `sanction` database). The legacy French schema is renamed to English on the same connection through the migrations below.

---

## 1. Commands

Run from the project root **after** the module path is registered in the root `composer.json` (`./modules/Offenses` repository entry + `acmarche/offenses` requirement).

```bash
composer dump-autoload
php artisan migrate --path=modules/Offenses/database/migrations --database=maria-offenses --no-interaction
vendor/bin/pint --dirty --format agent
```

No `php artisan make:filament-resource` command — the module already ships hand-rolled v5 resources following the project's existing per-module pattern (see `modules/Mediation` for reference).

---

## 2. Models

All models share the `maria-offenses` connection (declared via `#[Connection]` attribute) and use PHP 8 `#[Fillable]` plus the protected `casts()` method. Connection config: `modules/Offenses/config/database.php`.

### `AcMarche\Offenses\Models\OffenseAct`
Table: `offense_acts` (legacy `acte`).

| Attribute | Type | Cast | Notes |
| --- | --- | --- | --- |
| `id` | int |  | PK |
| `name` | string |  | Required, max 255 — formerly `nom` |
| `user_add` | string |  | Author username — formerly `user` |
| `created_at` | datetime | datetime |  |
| `updated_at` | datetime | datetime |  |

Relationship:
- `offenses(): HasMany` → `Offense::class`

### `AcMarche\Offenses\Models\Offender`
Table: `offenders` (legacy `contrevenant`).

| Attribute | Type | Cast | Notes |
| --- | --- | --- | --- |
| `id` | int |  | PK |
| `slug` | string(70) |  | Unique — formerly `slugname` |
| `last_name` | string |  | Required — formerly `nom` |
| `first_name` | string |  | Required — formerly `prenom` |
| `birth_date` | date | date | Nullable — formerly `ne_le` |
| `street` | string |  | Nullable — formerly `rue` |
| `postal_code` | string |  | Nullable — formerly `code_postal` |
| `city` | string |  | Nullable — formerly `localite` |
| `user_add` | string |  | Nullable |
| `created_at` | datetime | datetime |  |
| `updated_at` | datetime | datetime |  |

Relationship:
- `offenses(): HasMany` → `Offense::class`

### `AcMarche\Offenses\Models\Offense`
Table: `offenses` (legacy `incivilite`).

| Attribute | Type | Cast | Notes |
| --- | --- | --- | --- |
| `id` | int |  | PK |
| `offender_id` | foreignId | int | Nullable, `nullOnDelete` — formerly `contrevenant_id` |
| `offense_act_id` | foreignId | int | Nullable, `nullOnDelete` — formerly `acte_id` |
| `decision_date` | date | date | Nullable — formerly `date_decision` |
| `fine_amount` | double | float | Nullable — formerly `amende` |
| `mediation` | bool | boolean | Default false |
| `prosecutor_opinion` | string |  | Nullable — formerly `avis_procureur` |
| `file_name` | string |  | Nullable — formerly `fileName` |
| `user_add` | string |  | Nullable |
| `created_at` | datetime | datetime |  |
| `updated_at` | datetime | datetime |  |

Relationships:
- `offender(): BelongsTo` → `Offender::class`
- `offenseAct(): BelongsTo` → `OffenseAct::class`

### Migrations

Three migration files under `modules/Offenses/database/migrations/`. All run against the `maria-offenses` connection. Each migration follows the project's "rename if legacy table exists, otherwise create from scratch" idiom (see `modules/Mediation/database/migrations/2025_05_02_000003_create_case_files_table.php`).

| Order | File | Up |
| --- | --- | --- |
| 1 | `2026_05_04_000001_create_offense_acts_table.php` | rename `acte` → `offense_acts` and rename columns; else `create` |
| 2 | `2026_05_04_000002_create_offenders_table.php` | rename `contrevenant` → `offenders` and rename columns; else `create` |
| 3 | `2026_05_04_000003_create_offenses_table.php` | rename `incivilite` → `offenses` and rename columns; else `create` |

---

## 3. Resources

### Panel

`AcMarche\Offenses\Providers\Filament\OffensesPanelProvider`

- ID: `offenses-panel`
- Path: `/offenses`
- Brand: `Sanctions administratives`
- Primary color: `Color::Red`
- Discovers resources from `src/Filament/Resources` for namespace `AcMarche\Offenses\Filament\Resources`
- Uses the same middleware pipeline as `MediationPanelProvider`

Register the panel provider class in `bootstrap/providers.php` after `MediationPanelProvider`.

### Resource: `OffenseResource` (main list — sanctions)

- Location: `modules/Offenses/src/Filament/Resources/Offenses/OffenseResource.php`
- Model: `AcMarche\Offenses\Models\Offense`
- Navigation icon: `heroicon-o-shield-exclamation`
- Navigation label: `Sanctions`
- Navigation sort: 1
- Pages: `index`, `create`, `view` (`/{record}/view`), `edit` (`/{record}/edit`)
- Docs: https://filamentphp.com/docs/5.x/resources/getting-started

#### Form (`Schemas/OffenseForm`)

Layout: single `Filament\Schemas\Components\Section` titled "Sanction", `columns(2)`.

| Field | Component | Docs | Validation | Config |
| --- | --- | --- | --- | --- |
| Contrevenant | `Filament\Forms\Components\Select` | https://filamentphp.com/docs/5.x/forms/select | required | `relationship('offender', 'last_name')`, `getOptionLabelFromRecordUsing(fn (Offender $r) => trim($r->last_name.' '.$r->first_name))`, `searchable(['last_name','first_name'])`, `preload()` |
| Acte | `Filament\Forms\Components\Select` | https://filamentphp.com/docs/5.x/forms/select | required | `relationship('offenseAct', 'name')`, `searchable()`, `preload()` |
| Date de décision | `Filament\Forms\Components\DatePicker` | https://filamentphp.com/docs/5.x/forms/date-time-picker | nullable |  |
| Amende (€) | `Filament\Forms\Components\TextInput` | https://filamentphp.com/docs/5.x/forms/text-input | nullable | `numeric()`, `step(0.01)` |
| Médiation | `Filament\Forms\Components\Toggle` | https://filamentphp.com/docs/5.x/forms/toggle |  | `default(false)` |
| Avis du procureur | `Filament\Forms\Components\TextInput` |  | max:255 |  |
| Fichier | `Filament\Forms\Components\TextInput` |  | max:255 | `columnSpanFull()` |

#### Table (`Tables/OffenseTables`)

`defaultSort('decision_date', 'desc')`, `defaultPaginationPageOption(50)`.

| Column | Component | Docs | Config |
| --- | --- | --- | --- |
| Décision | `Filament\Tables\Columns\TextColumn` | https://filamentphp.com/docs/5.x/tables/columns/text | `date('d/m/Y')`, `sortable()`, links to view URL |
| Nom | `TextColumn` |  | `make('offender.last_name')`, searchable, sortable |
| Prénom | `TextColumn` |  | `make('offender.first_name')`, searchable, sortable |
| Acte | `TextColumn` |  | `make('offenseAct.name')`, searchable, sortable |
| Amende | `TextColumn` |  | `money('EUR')`, sortable, placeholder `—` |
| Médiation | `Filament\Tables\Columns\IconColumn` |  | `boolean()` |
| Avis procureur | `TextColumn` |  | `toggleable(isToggledHiddenByDefault: true)` |

Filters:

| Filter | Component | Docs | Config |
| --- | --- | --- | --- |
| Acte | `Filament\Tables\Filters\SelectFilter` | https://filamentphp.com/docs/5.x/tables/filters | `relationship('offenseAct', 'name')`, `searchable()`, `preload()` |
| Médiation | `Filament\Tables\Filters\TernaryFilter` |  |  |
| Avec amende | `Filament\Tables\Filters\Filter` |  | `query(fn (Builder $q) => $q->whereNotNull('fine_amount'))` |

Record actions: `Filament\Actions\ViewAction`, `Filament\Actions\EditAction`.
Bulk actions: `Filament\Actions\BulkActionGroup` containing `Filament\Actions\DeleteBulkAction`.

#### Infolist (`Schemas/OffenseInfolist`)

Three `Filament\Schemas\Components\Section`:

1. **Sanction** (2 cols) — `offenseAct.name`, `decision_date` (date), `fine_amount` (money EUR), `mediation` (`Filament\Infolists\Components\IconEntry::boolean()`), `prosecutor_opinion`, `file_name`.
2. **Contrevenant** (2 cols) — `offender.last_name`, `offender.first_name`, `offender.birth_date` (date), `offender.street`, `offender.postal_code`, `offender.city`.
3. **Métadonnées** (3 cols, `collapsed()`) — `user_add`, `created_at` (`dateTime('d/m/Y H:i')`), `updated_at`.

### Resource: `OffenderResource`

- Location: `modules/Offenses/src/Filament/Resources/Offenders/OffenderResource.php`
- Navigation icon: `heroicon-o-user`, label `Contrevenants`, sort 2.

#### Form

Two sections, both 3-cols max:

| Section | Field | Component | Validation |
| --- | --- | --- | --- |
| Identité | Nom | `TextInput` make `last_name` | required, max:255 |
| Identité | Prénom | `TextInput` make `first_name` | required, max:255 |
| Identité | Date de naissance | `DatePicker` make `birth_date` | nullable |
| Adresse | Rue | `TextInput` make `street` | max:255 |
| Adresse | Code postal | `TextInput` make `postal_code` | max:20 |
| Adresse | Localité | `TextInput` make `city` | max:255 |

#### Table

`defaultSort('last_name')`, `defaultPaginationPageOption(50)`.

| Column | Notes |
| --- | --- |
| `last_name` | searchable, sortable, links to view |
| `first_name` | searchable, sortable |
| `birth_date` | `date('d/m/Y')`, toggleable hidden |
| `city` | searchable, sortable |
| `postal_code` | toggleable hidden |
| `offenses_count` | `counts('offenses')`, sortable |

Record actions: `ViewAction`, `EditAction`. Bulk: `DeleteBulkAction`.

### Resource: `OffenseActResource`

- Location: `modules/Offenses/src/Filament/Resources/OffenseActs/OffenseActResource.php`
- Navigation icon: `heroicon-o-tag`, label `Types d'actes`, sort 3.

#### Form

| Field | Component | Validation |
| --- | --- | --- |
| Nom | `TextInput` make `name` | required, max:255 |

#### Table

`defaultSort('name')`. Columns: `name` (searchable, sortable, links to view), `offenses_count` (counts). Record actions: `ViewAction`, `EditAction`. Bulk: `DeleteBulkAction`.

---

## 4. Authorization

Access is gated by **panel authentication** only — same model as the Mediation module. No per-resource policies are introduced in this iteration.

- Panel access: `Filament\Http\Middleware\Authenticate` (already wired in `OffensesPanelProvider::panel()`).
- All authenticated users may read/write all resources.

If finer-grained access is required later, add a `Modules` row to the Security DB referencing the new panel and wire policies in a follow-up plan.

---

## 5. Widgets

None for v1.

---

## 6. Tests

Pest feature tests under `modules/Offenses/tests/Feature/`. Use `RefreshDatabase` only on the `maria-offenses` connection (the test should explicitly run migrations against that connection — see `modules/Mediation/tests` for the project pattern when adding tests in a future iteration).

| Test | Asserts |
| --- | --- |
| `OffenseListTest::it_shows_offenses_table` | `livewire(ListOffenses::class)->assertCanSeeTableRecords($offenses)` |
| `OffenseCreateTest::it_creates_offense` | `livewire(CreateOffense::class)->fillForm([...])->call('create')->assertHasNoFormErrors()` |
| `OffenseEditTest::it_updates_offense` | `livewire(EditOffense::class, ['record' => $id])->fillForm([...])->call('save')` |
| `OffenderListTest::it_shows_offenders` |  |
| `OffenseActListTest::it_shows_acts` |  |

Each test must call `$this->actingAs(User::factory()->create())` before instantiating the Livewire component (Filament panel guard requirement).

---

## Pre-submission checklist

- [x] Models defined with full attributes and relationships (§2)
- [x] Migrations specified with rename-or-create idiom matching existing modules (§2)
- [x] Each form field lists Component (full namespace), Docs URL, Validation, Config (§3)
- [x] Each column lists Component, Docs URL, Config (§3)
- [x] Each filter lists Component, Docs URL, Config (§3)
- [x] Authorization decisions explicit (§4)
- [x] Tests scoped (§6)
- [x] No reactive fields → no Get/Set imports needed
- [x] Panel provider registered in `bootstrap/providers.php`
- [x] Module repository + requirement registered in root `composer.json`
- [x] `.env` updated with `DB_OFFENSES_*` keys
