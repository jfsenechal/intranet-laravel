# StreetWatch Module — Filament v5 Implementation Blueprint

> Spec for an implementing agent. Follow the patterns of `modules/CpasLibrary`
> (composer.json, ServiceProvider, PanelProvider, RolesEnum, Policies). All
> components below include full namespaces and docs URLs. The implementing
> agent must NOT make decisions outside what is specified here; ask the user
> first.

Source schema: `data/dumpsql/street.sql` — tables `incidents`, `requests_by`,
`types`. The legacy database `street` is a small log of street-work incidents
collected by Marche-en-Famenne CPAS street workers.

Module characteristics:
- Namespace: `AcMarche\StreetWatch`
- Composer package: `acmarche/street-watch`
- DB connection: `maria-street-watch` (own MySQL/MariaDB database, default name `street`)
- Filament panel id: `street-watch-panel`, path: `street-watch`
- Roles: `ROLE_STREET_WATCH` (single role — all members can view/create; only
  the incident's author or an application administrator can update/delete)

---

## 0. Setup Commands

Run from project root **in order**:

```bash
# After editing composer.json + bootstrap/providers.php + tests/{TestCase,Pest}.php
composer update acmarche/street-watch

# Migrations
php artisan migrate --database=maria-street-watch --path=modules/StreetWatch/database/migrations

# Filament scaffolds (run inside main app, then move files into module — see Section 4)
php artisan make:filament-resource Incident --view --generate --no-interaction
php artisan make:filament-resource RequestBy --view --generate --no-interaction
php artisan make:filament-resource TypeIncident --view --generate --no-interaction
```

Move generated files from `app/Filament/Resources/*` into
`modules/StreetWatch/src/Filament/Resources/*` and update namespaces from
`App\Filament\Resources\…` to `AcMarche\StreetWatch\Filament\Resources\…`.

---

## 1. Module Skeleton

Create the same directory layout as `modules/CpasLibrary`:

```
modules/StreetWatch/
├── composer.json
├── config/
│   ├── street-watch.php
│   └── database.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
└── src/
    ├── Enums/RolesEnum.php
    ├── Filament/Resources/
    │   ├── Incidents/
    │   ├── RequestsBy/
    │   └── TypesIncident/
    ├── Models/
    ├── Policies/
    └── Providers/
        ├── StreetWatchServiceProvider.php
        └── Filament/StreetWatchPanelProvider.php
```

### 1.1 `modules/StreetWatch/composer.json`

Mirror `modules/CpasLibrary/composer.json` with:
- `"name": "acmarche/street-watch"`
- PSR-4 root: `"AcMarche\\StreetWatch\\": "src/"`
- Factories: `"AcMarche\\StreetWatch\\Database\\Factories\\": "database/factories/"`
- Seeders: `"AcMarche\\StreetWatch\\Database\\Seeders\\": "database/seeders/"`
- Tests: `"AcMarche\\StreetWatch\\Tests\\": "tests"`
- Provider: `AcMarche\StreetWatch\Providers\StreetWatchServiceProvider`

### 1.2 Register in root `/var/www/intranet-laravel/composer.json`

Add to the `repositories` array:
```json
{ "type": "path", "url": "./modules/StreetWatch" }
```
Add to `require`: `"acmarche/street-watch": "*@dev"`.

### 1.3 Register Filament panel

Add `AcMarche\StreetWatch\Providers\Filament\StreetWatchPanelProvider::class`
to `bootstrap/providers.php`.

### 1.4 `config/database.php`

Mirror `modules/CpasLibrary/config/database.php` with:
- `'connection' => 'maria-street-watch'`
- Connection key `maria-street-watch`
- Env vars: `DB_STREET_WATCH_DRIVER`, `DB_STREET_WATCH_HOST`, `DB_STREET_WATCH_PORT`,
  `DB_STREET_WATCH_DATABASE` (default: `street`), `DB_STREET_WATCH_USERNAME`,
  `DB_STREET_WATCH_PASSWORD`, `DB_STREET_WATCH_SOCKET`.

### 1.5 `config/street-watch.php`
```php
<?php
declare(strict_types=1);
return [];
```

### 1.6 ServiceProvider

`src/Providers/StreetWatchServiceProvider.php` — mirror
`CpasLibraryServiceProvider`:
- Namespace `AcMarche\StreetWatch\Providers`
- Class name `StreetWatchServiceProvider`
- `public static int $module_id = 53;` (next free id after CpasLibrary=52)
- `moduleName()` returns `'street-watch'`
- Policies auto-discovered (no explicit `Gate::policy(...)` calls — matches
  the CpasLibrary pattern; Laravel auto-maps
  `AcMarche\StreetWatch\Models\X` → `AcMarche\StreetWatch\Policies\XPolicy`).

### 1.7 Panel Provider

`src/Providers/Filament/StreetWatchPanelProvider.php` — mirror
`CpasLibraryPanelProvider` and change:
- Class: `StreetWatchPanelProvider`
- `->id('street-watch-panel')`
- `->path('street-watch')`
- `->brandName('Travail de rue')`
- `->colors(['primary' => Color::Rose])`
- Discovery namespaces switched to `AcMarche\\StreetWatch\\Filament\\…`

### 1.8 Test wiring

- Append `'maria-street-watch'` to `MODULE_CONNECTIONS` in
  `tests/TestCase.php`.
- Append the two folders to `tests/Pest.php` `uses(...)` block:
  ```
  '../modules/StreetWatch/tests/Feature',
  '../modules/StreetWatch/tests/Unit',
  ```

---

## 2. Enums

### 2.1 RolesEnum

Location: `src/Enums/RolesEnum.php`
```php
<?php
declare(strict_types=1);
namespace AcMarche\StreetWatch\Enums;

enum RolesEnum: string
{
    case ROLE_STREET_WATCH = 'ROLE_STREET_WATCH';
}
```

---

## 3. Models

> **Important**: target DB uses Symfony Doctrine-style column names
> (`createdAt`, `updatedAt`, `user_add`, `occurred_date`, `requestBy_id`,
> `typeIncident_id`). Keep the existing column names in migrations and
> declare matching `$casts`/`getters` — do NOT rename them. The schema must
> stay 1-for-1 compatible with the legacy `street` database.

### 3.1 Model: RequestBy

```
Model: RequestBy
  Connection: maria-street-watch
  Table: requests_by
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - name: string(255), required
  Relationships:
    - hasMany: Incident via requestBy_id  (alias: incidents)
  Fillable: [name]
```

### 3.2 Model: TypeIncident

```
Model: TypeIncident
  Connection: maria-street-watch
  Table: types
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - name: string(255), required
  Relationships:
    - hasMany: Incident via typeIncident_id  (alias: incidents)
  Fillable: [name]
```

### 3.3 Model: Incident

```
Model: Incident
  Connection: maria-street-watch
  Table: incidents
  Timestamps: createdAt / updatedAt    // const CREATED_AT = 'createdAt'; UPDATED_AT = 'updatedAt'
  Attributes:
    - id: bigint, primary
    - place: string(255), required
    - object: string(255), required
    - description: longtext, required
    - response: longtext, nullable
    - user_add: string(255), required
    - occurred_date: datetime, nullable
    - createdAt: datetime, nullable
    - updatedAt: datetime, nullable
    - requestBy_id: integer, required, FK requests_by.id, restrict on delete
    - typeIncident_id: integer, required, FK types.id, restrict on delete
  Indexes:
    - KEY (requestBy_id)
    - KEY (typeIncident_id)
  Casts:
    - occurred_date => datetime
    - createdAt => datetime
    - updatedAt => datetime
  Relationships:
    - belongsTo: RequestBy via requestBy_id      (alias: requestBy)
    - belongsTo: TypeIncident via typeIncident_id (alias: typeIncident)
  Fillable:
    [place, object, description, response, user_add, occurred_date,
     requestBy_id, typeIncident_id]
  Boot:
    - on creating: if user_add empty AND auth()->check(),
       set user_add = auth()->user()->username ?? auth()->user()->email
```

---

## 4. Migrations

All migrations declare `protected $connection = 'maria-street-watch';` and
wrap creation in `if (Schema::connection(...)->hasTable(...)) return;` —
mirror `modules/CpasLibrary/database/migrations/2026_05_16_120000_create_categorie_table.php`.

Order (timestamps):

1. `2026_05_18_120000_create_requests_by_table.php` — columns per Model 3.1.
2. `2026_05_18_120001_create_types_table.php` — columns per Model 3.2.
3. `2026_05_18_120002_create_incidents_table.php`:
   - All columns per Model 3.3 with legacy names preserved exactly (camelCase
     for `createdAt`, `updatedAt`, `requestBy_id`, `typeIncident_id`;
     snake_case for `user_add`, `occurred_date`).
   - FK `requestBy_id` → `requests_by.id`, `restrictOnDelete()`.
   - FK `typeIncident_id` → `types.id`, `restrictOnDelete()`.
   - `$table->index('requestBy_id')` and `$table->index('typeIncident_id')`.

---

## 5. Factories & Seeders

### 5.1 Factories (under `database/factories/`)

- `RequestByFactory` — `name => fake()->unique()->words(2, true)`.
- `TypeIncidentFactory` — `name => fake()->unique()->word()`.
- `IncidentFactory` — `place => fake()->streetName()`, `object => fake()->sentence(4)`,
  `description => fake()->paragraph()`, `response => null`,
  `user_add => fake()->userName()`, `occurred_date => fake()->dateTimeThisYear()`,
  `requestBy_id => RequestByFactory::new()`, `typeIncident_id => TypeIncidentFactory::new()`,
  `createdAt => now()`, `updatedAt => now()`.

### 5.2 Seeders

`StreetWatchSeeder` — empty by default; placeholder for future dev data.

---

## 6. Policies

Location: `src/Policies/`. Three policies, one per resource model. The two
lookup tables (RequestBy, TypeIncident) are admin-only for create/update/delete
but any role member can list+view. The Incident policy allows any role member
to view+create; update/delete is restricted to the owner (matching `user_add ===
$user->username`) or an application administrator (`$user->isAdministrator()`).

```
Policy: IncidentPolicy
  Location: AcMarche\StreetWatch\Policies\IncidentPolicy
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview#authorization

  Abilities:
    viewAny:   hasRole
    view:      hasRole
    create:    hasRole
    update:    isAdmin OR (hasRole AND $incident->user_add === $user->username)
    delete:    isAdmin OR (hasRole AND $incident->user_add === $user->username)
    restore:   false
    forceDelete: false

  Helper methods:
    hasRole(User $user): bool
      - $user->isAdministrator() => true
      - $user->hasOneOfThisRoles([RolesEnum::ROLE_STREET_WATCH->value]) => true
      - else false

    isAdmin(User $user): bool
      - $user->isAdministrator() => true
      - else false
```

`RequestByPolicy` & `TypeIncidentPolicy`:

```
Abilities:
  viewAny:  hasRole
  view:     hasRole
  create:   isAdmin
  update:   isAdmin
  delete:   isAdmin
  restore:  false
  forceDelete: false
```

---

## 7. Resources

### 7.1 Resource: IncidentResource

```
Resource: IncidentResource
  Command: php artisan make:filament-resource Incident --view --generate --no-interaction
  Location: AcMarche\StreetWatch\Filament\Resources\Incidents\IncidentResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: object
  GloballySearchableAttributes: [place, object, description, response]

  Navigation:
    Group: Travail de rue
    Icon: Heroicon::OutlinedExclamationTriangle
    Sort: 1

  Form:
    Columns: 2

    Section: Identification (ColumnSpan: full, Columns: 2)
      Field: place
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: required, max:255
        Config: ->required(), ->maxLength(255), ->label('Lieu')

      Field: object
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: required, max:255
        Config: ->required(), ->maxLength(255), ->label('Objet')

      Field: typeIncident_id
        Component: Filament\Forms\Components\Select
        Docs: https://filamentphp.com/docs/5.x/forms/select
        Validation: required, exists:types,id
        Config: ->relationship('typeIncident', 'name'),
                ->required(), ->searchable(), ->preload(),
                ->label('Type')

      Field: requestBy_id
        Component: Filament\Forms\Components\Select
        Docs: https://filamentphp.com/docs/5.x/forms/select
        Validation: required, exists:requests_by,id
        Config: ->relationship('requestBy', 'name'),
                ->required(), ->searchable(), ->preload(),
                ->label('Demandé par')

      Field: occurred_date
        Component: Filament\Forms\Components\DateTimePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date
        Config: ->label("Date de l'incident"), ->seconds(false)

    Section: Contenu (ColumnSpan: full, Columns: 1)
      Field: description
        Component: Filament\Forms\Components\RichEditor
        Docs: https://filamentphp.com/docs/5.x/forms/rich-editor
        Validation: required
        Config: ->required(), ->columnSpanFull(), ->label('Description')

      Field: response
        Component: Filament\Forms\Components\RichEditor
        Docs: https://filamentphp.com/docs/5.x/forms/rich-editor
        Validation: nullable
        Config: ->columnSpanFull(), ->label('Suite donnée')

  Table:
    DefaultSort: createdAt desc

    Column: occurred_date
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->dateTime('d/m/Y'), ->sortable(), ->label("Date de l'incident"),
              ->placeholder('—')

    Column: place
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable(), ->limit(40), ->label('Lieu')

    Column: object
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable(), ->wrap(), ->limit(60), ->label('Objet')

    Column: typeIncident.name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->badge(), ->label('Type'), ->toggleable()

    Column: requestBy.name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Demandé par'), ->toggleable()

    Column: user_add
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Auteur'), ->toggleable(), ->searchable()

    Column: createdAt
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->dateTime('d/m/Y H:i'), ->sortable(), ->label('Créé le'),
              ->toggleable(isToggledHiddenByDefault: true)

    Filter: typeIncident_id
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->relationship('typeIncident', 'name'), ->searchable(),
              ->preload(), ->label('Type')

    Filter: requestBy_id
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->relationship('requestBy', 'name'), ->searchable(),
              ->preload(), ->label('Demandé par')

    Filter: user_add
      Component: Filament\Tables\Filters\Filter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/custom
      Config: ->schema([TextInput::make('user_add')->label('Auteur')])
              ->query(fn (Builder $q, array $data) =>
                $q->when($data['user_add'] ?? null,
                        fn ($q, $v) => $q->where('user_add', $v)))

    Filter: has_response
      Component: Filament\Tables\Filters\TernaryFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/ternary
      Config: ->label('Avec suite'), ->nullable(),
              ->queries(true:  fn (Builder $q) => $q->whereNotNull('response'),
                        false: fn (Builder $q) => $q->whereNull('response'),
                        blank: fn (Builder $q) => $q)

  RowActions: ViewAction, EditAction
  BulkActions: DeleteBulkAction

  Infolist:
    Section: Identification (ColumnSpan: full, Columns: 2)
      Entry: place
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Lieu'), ->weight('bold')
      Entry: object
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Objet'), ->columnSpanFull()
      Entry: typeIncident.name
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Type'), ->badge()
      Entry: requestBy.name
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Demandé par')
      Entry: occurred_date
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label("Date de l'incident"), ->dateTime('d/m/Y')

    Section: Contenu (ColumnSpan: full, Columns: 1)
      Entry: description
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Description'), ->html(), ->columnSpanFull()
      Entry: response
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Suite donnée'), ->html(), ->columnSpanFull(),
                ->placeholder('—')

    Section: Méta (ColumnSpan: full, Columns: 3)
      Entry: user_add
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Auteur')
      Entry: createdAt
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Créé le'), ->dateTime()
      Entry: updatedAt
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Modifié le'), ->dateTime()

  Authorization: see IncidentPolicy (Section 6)
```

### 7.2 Resource: RequestByResource

```
Resource: RequestByResource
  Command: php artisan make:filament-resource RequestBy --view --generate --no-interaction
  Location: AcMarche\StreetWatch\Filament\Resources\RequestsBy\RequestByResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: name
  Model: AcMarche\StreetWatch\Models\RequestBy

  Navigation:
    Group: Travail de rue
    Icon: Heroicon::OutlinedUserGroup
    Sort: 2

  Form:
    Field: name
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: required, max:255, unique:requests_by,name,{{record}}
      Config: ->required(), ->maxLength(255), ->unique(ignoreRecord: true),
              ->label('Nom')

  Table:
    DefaultSort: name asc

    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable(), ->label('Nom')

    Column: incidents_count
      Component: Filament\Tables\Columns\TextColumn
      Config: ->counts('incidents'), ->label('Incidents'), ->sortable()
      Note: requires `incidents()` HasMany on the model.

  Infolist:
    Entry: name
      Component: Filament\Infolists\Components\TextEntry
      Config: ->label('Nom')

  Authorization: see RequestByPolicy (Section 6)
```

### 7.3 Resource: TypeIncidentResource

```
Resource: TypeIncidentResource
  Command: php artisan make:filament-resource TypeIncident --view --generate --no-interaction
  Location: AcMarche\StreetWatch\Filament\Resources\TypesIncident\TypeIncidentResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: name
  Model: AcMarche\StreetWatch\Models\TypeIncident

  Navigation:
    Group: Travail de rue
    Icon: Heroicon::OutlinedTag
    Sort: 3

  Form:
    Field: name
      Component: Filament\Forms\Components\TextInput
      Validation: required, max:255, unique:types,name,{{record}}
      Config: ->required(), ->maxLength(255), ->unique(ignoreRecord: true),
              ->label('Nom')

  Table:
    DefaultSort: name asc

    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable(), ->label('Nom')

    Column: incidents_count
      Component: Filament\Tables\Columns\TextColumn
      Config: ->counts('incidents'), ->label('Incidents'), ->sortable()

  Infolist:
    Entry: name
      Component: Filament\Infolists\Components\TextEntry
      Config: ->label('Nom')

  Authorization: see TypeIncidentPolicy (Section 6)
```

---

## 8. Tests

Location: `modules/StreetWatch/tests/Feature/`. Use Pest 4 + Filament Livewire
helpers (already available; `pestphp/pest-plugin-livewire` is in root composer).

For each Livewire test, in `beforeEach`:
```php
Filament::setCurrentPanel(Filament::getPanel('street-watch-panel'));
$this->member = User::factory()->create(['username' => 'member1']);
$role = Role::factory()->create(['name' => RolesEnum::ROLE_STREET_WATCH->value]);
$this->member->roles()->attach($role);
$this->actingAs($this->member);
```

### 8.1 IncidentResourceTest

```
Pages render:
  - ListIncidents, CreateIncident, ViewIncident, EditIncident assertOk()

CRUD:
  - creates an incident: fillForm + ->call('create') sets user_add = current
    user's username, persists row in incidents
  - updates an incident the user authored
  - deletes an incident the user authored

Filtering & sorting:
  - default sort is createdAt desc
  - typeIncident_id filter restricts results
  - requestBy_id filter restricts results
  - has_response=true returns only rows with response not null

Validation (dataset pattern):
  - place is required (max:255)
  - object is required (max:255)
  - description is required
  - requestBy_id is required and must exist in requests_by
  - typeIncident_id is required and must exist in types

Authorization:
  - stranger (no role) → cannot access ListIncidents (assertForbidden)
  - member can list, view, create
  - member cannot edit an incident authored by someone else (assertForbidden)
  - member can edit an incident they authored
```

### 8.2 RequestByResourceTest

```
Pages render: List, Create, View, Edit assertOk() when acting as admin

Authorization:
  - stranger (no role) → assertForbidden on ListRequestsBy
  - member (ROLE_STREET_WATCH only) can list+view but assertForbidden on Create
  - admin (application administrator) can create+edit+delete

Validation:
  - name is required (max:255)
  - name must be unique
```

### 8.3 TypeIncidentResourceTest

Same shape as 8.2 against `types` table.

### 8.4 PoliciesTest

Plain feature tests for `Gate::allows(...)` per role × ability matrix:
- ROLE_STREET_WATCH passes viewAny/view/create on Incident; only the author
  passes update/delete; non-author fails.
- isAdministrator() (no role) passes everything.
- Stranger fails everything.
- ROLE_STREET_WATCH fails create/update/delete on RequestBy and TypeIncident.

---

## 9. Navigation & UX

- All three resources share the navigation group `'Travail de rue'`.
- Heroicon namespace: `Filament\Support\Icons\Heroicon`.
- Panel brand: `'Travail de rue'`, color `Color::Rose`.
- Edit/Create redirect to the View page (already set in the panel provider).
- French labels everywhere.

---

## 10. Checklist (must pass before considering plan implemented)

- [ ] Module loads via `composer update acmarche/street-watch`
- [ ] `php artisan migrate --database=maria-street-watch` succeeds
- [ ] `/street-watch` panel renders for a user with `ROLE_STREET_WATCH`
- [ ] `/street-watch` panel rejects users with no street-watch role
- [ ] All three resources list, create, view, edit, delete in the browser
- [ ] Incident.user_add is auto-filled with the authenticated user's username on create
- [ ] Member cannot edit/delete incidents owned by a different user
- [ ] Member cannot create RequestBy / TypeIncident (admin only)
- [ ] Tests pass: `php artisan test --compact --filter=StreetWatch`
- [ ] `vendor/bin/pint --dirty --format agent` produces no changes
- [ ] No new top-level folders outside `modules/StreetWatch/`