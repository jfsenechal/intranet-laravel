# ActivityManager Module — Filament v5 Implementation Blueprint

> Spec for an implementing agent. Follow the patterns of `modules/Conseil`,
> `modules/CpasLibrary`, and `modules/College` (composer.json, ServiceProvider,
> PanelProvider, RolesEnum, Policies). All components below include full
> namespaces and docs URLs. The implementing agent must NOT make decisions
> outside what is specified here; ask the user first.

Source schema: `data/dumpsql/mda.sql` (tables `activite`, `cours`, `dates_cours`,
`inscription`, `membre`). Legacy column names are preserved 1‑for‑1 with the
dump. Table names are kept singular as in the dump, matching the convention
already used by `modules/CpasLibrary` (`fiche`, `categorie`) and
`modules/College` (`destinataire`, `notification`).

Module characteristics:
- Namespace: `AcMarche\ActivityManager`
- Composer package: `acmarche/activity-manager`
- DB connection: `maria-activity-manager` (own MySQL/MariaDB database, legacy
  name `mda`)
- Filament panel id: `activity-manager-panel`, path: `activity-manager`
- Roles: `ROLE_MDA_ADMIN` (single role — full CRUD for holders or app
  administrators)

---

## 0. Setup Commands

Run from project root **in order**:

```bash
# Register module in main composer.json (manual edit, see Section 1)
composer update acmarche/activity-manager

# Migrations
php artisan migrate --database=maria-activity-manager --path=modules/ActivityManager/database/migrations

# Filament scaffolds (run inside main app, then move files into module — see Section 7)
php artisan make:filament-resource Activite --view --generate --no-interaction
php artisan make:filament-resource Cours --view --generate --no-interaction
php artisan make:filament-resource Membre --view --generate --no-interaction
php artisan make:filament-relation-manager ActiviteResource cours nom --no-interaction
php artisan make:filament-relation-manager CoursResource datesCours jour --no-interaction
php artisan make:filament-relation-manager CoursResource membres nom --attach --no-interaction
php artisan make:filament-relation-manager MembreResource cours nom --attach --no-interaction
```

Move generated files from `app/Filament/Resources/*` into
`modules/ActivityManager/src/Filament/Resources/*` and update namespaces from
`App\Filament\Resources\…` to `AcMarche\ActivityManager\Filament\Resources\…`.

---

## 1. Module Skeleton

Create the same directory layout as `modules/Conseil`:

```
modules/ActivityManager/
├── composer.json
├── config/
│   ├── activity-manager.php
│   └── database.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
└── src/
    ├── Enums/
    │   ├── CiviliteEnum.php
    │   └── RolesEnum.php
    ├── Filament/Resources/
    │   ├── Activites/
    │   ├── Cours/
    │   └── Membres/
    ├── Models/
    ├── Policies/
    └── Providers/
        ├── ActivityManagerServiceProvider.php
        └── Filament/ActivityManagerPanelProvider.php
```

### 1.1 `modules/ActivityManager/composer.json`

Mirror `modules/Conseil/composer.json` exactly, with:
- `"name": "acmarche/activity-manager"`
- PSR-4 root: `"AcMarche\\ActivityManager\\": "src/"`
- Factories: `"AcMarche\\ActivityManager\\Database\\Factories\\": "database/factories/"`
- Seeders: `"AcMarche\\ActivityManager\\Database\\Seeders\\": "database/seeders/"`
- Provider: `AcMarche\ActivityManager\Providers\ActivityManagerServiceProvider`

### 1.2 Register in root `/var/www/intranet-laravel/composer.json`

Add to the `repositories` array:
```json
{ "type": "path", "url": "./modules/ActivityManager" }
```
Add to `require`: `"acmarche/activity-manager": "*@dev"`.

### 1.3 Register Filament panel

Add `AcMarche\ActivityManager\Providers\Filament\ActivityManagerPanelProvider::class`
to `bootstrap/providers.php` (follow how Conseil's panel is registered).

### 1.4 `config/database.php`

Mirror `modules/Conseil/config/database.php` with:
- `'connection' => 'maria-activity-manager'`
- Connection key `maria-activity-manager`
- Env vars: `DB_ACTIVITY_MANAGER_DRIVER`, `DB_ACTIVITY_MANAGER_HOST`,
  `DB_ACTIVITY_MANAGER_PORT`, `DB_ACTIVITY_MANAGER_DATABASE` (default: `mda`),
  `DB_ACTIVITY_MANAGER_USERNAME`, `DB_ACTIVITY_MANAGER_PASSWORD`,
  `DB_ACTIVITY_MANAGER_SOCKET`.

### 1.5 `config/activity-manager.php`
```php
<?php
declare(strict_types=1);
return [];
```

### 1.6 ServiceProvider

`src/Providers/ActivityManagerServiceProvider.php` — exact copy of
`ConseilServiceProvider` with:
- Namespace `AcMarche\ActivityManager\Providers`
- Class name `ActivityManagerServiceProvider`
- `public static int $module_id = 63;` (next free id after Ad=62; ask user
  if a registry exists before assigning)
- `moduleName()` returns `'activity-manager'`

### 1.7 Panel Provider

`src/Providers/Filament/ActivityManagerPanelProvider.php` — copy
`ConseilPanelProvider` and change:
- Class: `ActivityManagerPanelProvider`
- `->id('activity-manager-panel')`
- `->path('activity-manager')`
- `->brandName('Maison des Aînés')`
- `->colors(['primary' => Color::Amber])`
- Discovery namespaces switched to `AcMarche\\ActivityManager\\Filament\\…`

---

## 2. Enums

### 2.1 RolesEnum

Location: `src/Enums/RolesEnum.php`
```php
<?php
declare(strict_types=1);
namespace AcMarche\ActivityManager\Enums;

enum RolesEnum: string
{
    case ROLE_MDA_ADMIN = 'ROLE_MDA_ADMIN';
}
```

### 2.2 CiviliteEnum

Source column: `membre.civilite` (varchar(50), nullable). Values observed in
data: `Madame`, `Monsieur`. Treat as a closed enum with two cases; expand
later if needed.

Location: `src/Enums/CiviliteEnum.php`

```
Enum: CiviliteEnum
  Backing: string
  Implements: Filament\Support\Contracts\HasLabel
  Cases:
    - MADAME: value 'Madame', label 'Madame'
    - MONSIEUR: value 'Monsieur', label 'Monsieur'
```

---

## 3. Models

> **Important**: target DB uses Symfony Doctrine-style column names
> (`activite_id`, `cours_id`, `membre_id`, `inscrit_le`, `date_debut`,
> `date_fin`, `codepostal`, …). Keep the existing column names in migrations
> and declare matching `$casts`/`fillable` — do NOT rename them. The schema
> must stay 1-for-1 compatible with the legacy `mda` database.
>
> No model in this module uses Eloquent timestamps — the legacy dump has no
> `created_at` / `updated_at` columns. Set `public $timestamps = false;` on
> every model.

### 3.1 Model: Activite

```
Model: Activite
  Connection: maria-activity-manager
  Table: activite
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - nom: string(150), required
    - description: longtext, nullable
  Relationships:
    - hasMany: Cours via activite_id   (alias: cours)
  Fillable: [nom, description]
```

### 3.2 Model: Cours

```
Model: Cours
  Connection: maria-activity-manager
  Table: cours
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - nom: string(200), required
    - date_debut: date, required
    - date_fin: date, nullable
    - activite_id: bigint, foreign(activite.id), nullable, onDelete: setNull
  Indexes:
    - KEY (activite_id)
  Casts:
    - date_debut => date
    - date_fin => date
  Relationships:
    - belongsTo: Activite via activite_id            (alias: activite)
    - hasMany: DatesCours via cours_id               (alias: datesCours)
    - belongsToMany: Membre via inscription
        (pivotTable: inscription, foreign: cours_id, related: membre_id)
        (alias: membres)
  Fillable: [nom, date_debut, date_fin, activite_id]
```

### 3.3 Model: DatesCours

```
Model: DatesCours
  Connection: maria-activity-manager
  Table: dates_cours
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - cours_id: bigint, foreign(cours.id), required, onDelete: cascade
    - remarque: longtext, nullable
    - jour: datetime, required
  Indexes:
    - KEY (cours_id)
  Casts:
    - jour => datetime
  Relationships:
    - belongsTo: Cours via cours_id   (alias: cours)
  Fillable: [cours_id, remarque, jour]
```

No standalone Filament resource — managed via RelationManager on
`CoursResource` (see Section 7.4).

### 3.4 Model: Membre

```
Model: Membre
  Connection: maria-activity-manager
  Table: membre
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - civilite: string(50), nullable
    - nom: string(50), required
    - prenom: string(50), required
    - rue: string(150), nullable
    - numero: string(50), nullable
    - codepostal: integer, nullable
    - localite: string(50), nullable
    - gsm: string(50), nullable
    - telephone: string(50), nullable
    - email: string(50), nullable
    - enabled: boolean, required, default:true
    - remarque: longtext, nullable
    - inscrit_le: date, nullable
  Casts:
    - civilite => CiviliteEnum
    - enabled => boolean
    - inscrit_le => date
  Relationships:
    - belongsToMany: Cours via inscription
        (pivotTable: inscription, foreign: membre_id, related: cours_id)
        (alias: cours)
  Fillable:
    [civilite, nom, prenom, rue, numero, codepostal, localite, gsm,
     telephone, email, enabled, remarque, inscrit_le]
```

### 3.5 Pivot table: inscription

Composite unique key `(membre_id, cours_id)`. Both FKs cascade on delete via
the model relationships. Defined in:
- `Cours.membres()` `belongsToMany(Membre::class, 'inscription', 'cours_id', 'membre_id')`
- `Membre.cours()` `belongsToMany(Cours::class, 'inscription', 'membre_id', 'cours_id')`

No dedicated Eloquent model required.

---

## 4. Migrations

All migrations declare `protected $connection = 'maria-activity-manager';` and
wrap creation in `if (Schema::connection(...)->hasTable(...)) return;` —
mirror `modules/Conseil/database/migrations/2026_05_15_100000_create_groupes_table.php`.

Order (timestamps):

1. `2026_05_18_120000_create_activite_table.php` — per Model 3.1.
2. `2026_05_18_120001_create_cours_table.php` — per Model 3.2. FK `activite_id`
   references `activite.id` with `onDelete: setNull`.
3. `2026_05_18_120002_create_dates_cours_table.php` — per Model 3.3. FK
   `cours_id` references `cours.id` with `onDelete: cascade`.
4. `2026_05_18_120003_create_membre_table.php` — per Model 3.4.
5. `2026_05_18_120004_create_inscription_table.php`:
   - `$table->id();`
   - `$table->foreignId('membre_id')->nullable()->constrained('membre')->nullOnDelete();`
   - `$table->foreignId('cours_id')->nullable()->constrained('cours')->nullOnDelete();`
   - `$table->unique(['membre_id', 'cours_id']);`

---

## 5. Factories & Seeders

### 5.1 Factories (under `database/factories/`)

- `ActiviteFactory` — `nom => fake()->words(3, true)`,
  `description => fake()->optional()->paragraph()`.
- `CoursFactory` — `nom => fake()->sentence(6)`,
  `date_debut => fake()->dateTimeBetween('-2 years', 'now')`,
  `date_fin => fn (array $attrs) => fake()->dateTimeBetween($attrs['date_debut'], '+1 year')`,
  `activite_id => ActiviteFactory::new()`.
- `DatesCoursFactory` — `jour => fake()->dateTimeBetween('-1 year', '+1 year')`,
  `remarque => fake()->optional()->sentence()`,
  `cours_id => CoursFactory::new()`.
- `MembreFactory` — `civilite => fake()->randomElement(['Madame', 'Monsieur'])`,
  `nom => fake()->lastName()`, `prenom => fake()->firstName()`,
  `rue => fake()->streetName()`, `numero => fake()->buildingNumber()`,
  `codepostal => 6900`, `localite => 'Marche-en-Famenne'`,
  `gsm => fake()->phoneNumber()`, `telephone => fake()->phoneNumber()`,
  `email => fake()->unique()->safeEmail()`, `enabled => true`,
  `inscrit_le => fake()->dateTimeBetween('-5 years', 'now')`.

### 5.2 Seeders (under `database/seeders/`)

- `ActivityManagerSeeder` — registered manually; left empty by default. Used
  only for tests/dev.

---

## 6. Policies

Location: `src/Policies/`. Mirror `modules/Conseil/src/Policies/GroupePolicy.php`.
Three policies (one per resource model):

- `ActivitePolicy`
- `CoursPolicy`
- `MembrePolicy`

All share the same role gating logic (factor it into a `protected function
hasRole(User $user)` method in each policy — match Conseil's style, no shared
trait).

```
Policy: ActivitePolicy
  Location: AcMarche\ActivityManager\Policies\ActivitePolicy
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview#authorization

  Abilities (all delegate to hasRole($user)):
    viewAny:     hasRole
    view:        hasRole
    create:      hasRole
    update:      hasRole
    delete:      hasRole
    restore:     false
    forceDelete: false

  Helper methods:
    hasRole(User $user): bool
      - $user->isAdministrator() => true
      - $user->hasOneOfThisRoles([RolesEnum::ROLE_MDA_ADMIN->value]) => true
      - else false
```

`CoursPolicy` and `MembrePolicy` follow the exact same shape — single role
`ROLE_MDA_ADMIN` (or admin) gets full CRUD.

Register policies via `AuthServiceProvider`-style binding inside
`ActivityManagerServiceProvider::boot()` — use
`Gate::policy(Model::class, Policy::class)` (match the registration style
used in other modules; if other modules use auto-discovery, follow that).

---

## 7. Resources

### 7.1 Resource: ActiviteResource

```
Resource: ActiviteResource
  Command: php artisan make:filament-resource Activite --view --generate --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Activites\ActiviteResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: nom
  GloballySearchableAttributes: [nom, description]

  Navigation:
    Group: Activités
    Icon: Heroicon::OutlinedSparkles
    Sort: 1
    Label: Activités

  Form:
    Columns: 1

    Field: nom
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: required, max:150
      Config: ->required(), ->maxLength(150)

    Field: description
      Component: Filament\Forms\Components\Textarea
      Docs: https://filamentphp.com/docs/5.x/forms/textarea
      Validation: nullable
      Config: ->rows(4), ->columnSpanFull()

  Table:
    DefaultSort: nom asc

    Column: id
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->sortable(), ->toggleable(isToggledHiddenByDefault: true)

    Column: nom
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable()

    Column: description
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->limit(80), ->toggleable(), ->wrap()

    Column: cours_count
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->counts('cours'), ->label('Cours'), ->sortable()

  Infolist:
    Columns: 1
    Entry: nom
      Component: Filament\Infolists\Components\TextEntry
      Config: ->weight('bold')
    Entry: description
      Component: Filament\Infolists\Components\TextEntry
      Config: ->columnSpanFull(), ->placeholder('—')

  RelationManagers:
    - CoursRelationManager  (see Section 7.4)

  Authorization: see ActivitePolicy (Section 6)
```

### 7.2 Resource: CoursResource

```
Resource: CoursResource
  Command: php artisan make:filament-resource Cours --view --generate --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Cours\CoursResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: nom
  GloballySearchableAttributes: [nom]

  Navigation:
    Group: Activités
    Icon: Heroicon::OutlinedCalendarDays
    Sort: 2
    Label: Cours

  Form:
    Columns: 2

    Field: nom
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: required, max:200
      Config: ->required(), ->maxLength(200), ->columnSpanFull()

    Field: activite_id
      Component: Filament\Forms\Components\Select
      Docs: https://filamentphp.com/docs/5.x/forms/select
      Validation: nullable, exists:activite,id
      Config: ->relationship('activite', 'nom'), ->searchable(), ->preload(),
              ->label('Activité'),
              ->createOptionForm([
                TextInput::make('nom')->required()->maxLength(150),
                Textarea::make('description')->rows(3),
              ])

    Field: date_debut
      Component: Filament\Forms\Components\DatePicker
      Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
      Validation: required, date
      Config: ->required(), ->label('Date de début'),
              ->displayFormat('d/m/Y'), ->native(false)

    Field: date_fin
      Component: Filament\Forms\Components\DatePicker
      Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
      Validation: nullable, date, after_or_equal:date_debut
      Config: ->label('Date de fin'),
              ->displayFormat('d/m/Y'), ->native(false),
              ->afterOrEqual('date_debut')

  Table:
    DefaultSort: date_debut desc

    Column: nom
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable(), ->wrap(), ->limit(80)

    Column: activite.nom
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Activité'), ->sortable(), ->toggleable(), ->badge()

    Column: date_debut
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->date('d/m/Y'), ->sortable(), ->label('Début')

    Column: date_fin
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->date('d/m/Y'), ->sortable(), ->label('Fin'), ->placeholder('—')

    Column: membres_count
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->counts('membres'), ->label('Inscrits'), ->sortable()

    Column: dates_cours_count
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->counts('datesCours'), ->label('Séances'), ->toggleable()

    Filter: activite_id
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->relationship('activite', 'nom'), ->searchable(), ->preload(),
              ->label('Activité')

    Filter: en_cours
      Component: Filament\Tables\Filters\TernaryFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/ternary
      Config: ->label('En cours'), ->nullable(),
              ->queries(true:  fn (Builder $q) => $q->whereDate('date_debut', '<=', now())
                                                    ->where(fn ($q) => $q->whereNull('date_fin')
                                                                          ->orWhereDate('date_fin', '>=', now())),
                        false: fn (Builder $q) => $q->whereDate('date_fin', '<', now()),
                        blank: fn (Builder $q) => $q)

  Infolist:
    Columns: 2

    Section: Identification (ColumnSpan: full, Columns: 2)
      Entry: nom
        Component: Filament\Infolists\Components\TextEntry
        Config: ->columnSpanFull(), ->weight('bold')
      Entry: activite.nom
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Activité'), ->badge()
      Entry: date_debut
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date('d/m/Y'), ->label('Début')
      Entry: date_fin
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date('d/m/Y'), ->label('Fin'), ->placeholder('—')

  RelationManagers:
    - DatesCoursRelationManager  (see Section 7.4)
    - MembresRelationManager     (see Section 7.5)

  Authorization: see CoursPolicy (Section 6)
```

### 7.3 Resource: MembreResource

```
Resource: MembreResource
  Command: php artisan make:filament-resource Membre --view --generate --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Membres\MembreResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: nom
  GloballySearchableAttributes: [nom, prenom, email]

  Navigation:
    Group: Activités
    Icon: Heroicon::OutlinedUserGroup
    Sort: 3
    Label: Membres

  Form:
    Columns: 2

    Section: Identité (ColumnSpan: full)
      Columns: 2

      Field: civilite
        Component: Filament\Forms\Components\Select
        Docs: https://filamentphp.com/docs/5.x/forms/select
        Validation: nullable
        Config: ->options(CiviliteEnum::class), ->native(false)

      Field: enabled
        Component: Filament\Forms\Components\Toggle
        Docs: https://filamentphp.com/docs/5.x/forms/toggle
        Validation: required, boolean
        Config: ->default(true), ->label('Actif')

      Field: nom
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: required, max:50
        Config: ->required(), ->maxLength(50)

      Field: prenom
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: required, max:50
        Config: ->required(), ->maxLength(50)

      Field: inscrit_le
        Component: Filament\Forms\Components\DatePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date
        Config: ->label('Inscrit le'), ->displayFormat('d/m/Y'), ->native(false)

    Section: Adresse (ColumnSpan: full)
      Columns: 4

      Field: rue
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:150
        Config: ->maxLength(150), ->columnSpan(2)

      Field: numero
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:50
        Config: ->maxLength(50), ->label('N°')

      Field: codepostal
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, integer, digits_between:4,5
        Config: ->numeric(), ->label('Code postal')

      Field: localite
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:50
        Config: ->maxLength(50), ->columnSpan(2), ->label('Localité')

    Section: Contact (ColumnSpan: full)
      Columns: 3

      Field: gsm
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:50
        Config: ->tel(), ->maxLength(50), ->label('GSM')

      Field: telephone
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:50
        Config: ->tel(), ->maxLength(50), ->label('Téléphone')

      Field: email
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, email, max:50
        Config: ->email(), ->maxLength(50)

    Section: Notes (ColumnSpan: full, Columns: 1)
      Field: remarque
        Component: Filament\Forms\Components\Textarea
        Docs: https://filamentphp.com/docs/5.x/forms/textarea
        Validation: nullable
        Config: ->rows(4), ->columnSpanFull()

  Table:
    DefaultSort: nom asc

    Column: civilite
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->badge(), ->toggleable()

    Column: nom
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable()

    Column: prenom
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable(), ->label('Prénom')

    Column: localite
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->sortable(), ->toggleable(), ->label('Localité')

    Column: gsm
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->toggleable(), ->copyable()

    Column: email
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->toggleable(), ->copyable(), ->searchable()

    Column: enabled
      Component: Filament\Tables\Columns\IconColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/icon
      Config: ->boolean(), ->label('Actif'), ->sortable()

    Column: cours_count
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->counts('cours'), ->label('Cours'), ->sortable(),
              ->toggleable()

    Column: inscrit_le
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->date('d/m/Y'), ->sortable(), ->toggleable(),
              ->label('Inscrit le')

    Filter: civilite
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->options(CiviliteEnum::class)

    Filter: enabled
      Component: Filament\Tables\Filters\TernaryFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/ternary
      Config: ->label('Actif')

    Filter: localite
      Component: Filament\Tables\Filters\Filter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/custom
      Config: ->form([TextInput::make('localite')])
              ->query(fn (Builder $q, array $data) =>
                $q->when($data['localite'] ?? null,
                        fn ($q, $v) => $q->where('localite', 'like', "%{$v}%")))

  Infolist:
    Columns: 2

    Section: Identité (ColumnSpan: full, Columns: 2)
      Entry: civilite
        Component: Filament\Infolists\Components\TextEntry
        Config: ->badge()
      Entry: enabled
        Component: Filament\Infolists\Components\IconEntry
        Config: ->boolean(), ->label('Actif')
      Entry: nom
        Component: Filament\Infolists\Components\TextEntry
        Config: ->weight('bold')
      Entry: prenom
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Prénom')
      Entry: inscrit_le
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date('d/m/Y'), ->label('Inscrit le')

    Section: Adresse (ColumnSpan: full, Columns: 4)
      Entry: rue
        Component: Filament\Infolists\Components\TextEntry
        Config: ->columnSpan(2)
      Entry: numero
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('N°')
      Entry: codepostal
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Code postal')
      Entry: localite
        Component: Filament\Infolists\Components\TextEntry
        Config: ->columnSpan(2)

    Section: Contact (ColumnSpan: full, Columns: 3)
      Entry: gsm
        Component: Filament\Infolists\Components\TextEntry
        Config: ->copyable()
      Entry: telephone
        Component: Filament\Infolists\Components\TextEntry
        Config: ->copyable(), ->label('Téléphone')
      Entry: email
        Component: Filament\Infolists\Components\TextEntry
        Config: ->copyable()

    Section: Notes (ColumnSpan: full, Columns: 1)
      Entry: remarque
        Component: Filament\Infolists\Components\TextEntry
        Config: ->columnSpanFull(), ->placeholder('—')

  RelationManagers:
    - CoursRelationManager  (see Section 7.6)

  Authorization: see MembrePolicy (Section 6)
```

### 7.4 RelationManager: CoursRelationManager (on ActiviteResource)

```
RelationManager: CoursRelationManager
  Command: php artisan make:filament-relation-manager ActiviteResource cours nom --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Activites\RelationManagers\CoursRelationManager
  Relationship: cours (hasMany Cours)
  Title attribute: nom
  Docs: https://filamentphp.com/docs/5.x/panels/resources/relation-managers
  Can create: yes (auto-sets activite_id)
  Can edit: yes
  Can delete: yes

  Form: reuse CoursResource form (without activite_id field — set
        automatically via the relation)

  Table:
    Column: nom
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable(), ->limit(80), ->wrap()
    Column: date_debut
      Component: Filament\Tables\Columns\TextColumn
      Config: ->date('d/m/Y'), ->sortable(), ->label('Début')
    Column: date_fin
      Component: Filament\Tables\Columns\TextColumn
      Config: ->date('d/m/Y'), ->sortable(), ->label('Fin'),
              ->placeholder('—')
    Column: membres_count
      Component: Filament\Tables\Columns\TextColumn
      Config: ->counts('membres'), ->label('Inscrits')
```

### 7.4 RelationManager: DatesCoursRelationManager (on CoursResource)

```
RelationManager: DatesCoursRelationManager
  Command: php artisan make:filament-relation-manager CoursResource datesCours jour --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Cours\RelationManagers\DatesCoursRelationManager
  Relationship: datesCours (hasMany DatesCours)
  Title attribute: jour
  Can create: yes (auto-sets cours_id)
  Can edit: yes
  Can delete: yes

  Form:
    Field: jour
      Component: Filament\Forms\Components\DateTimePicker
      Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
      Validation: required, date
      Config: ->required(), ->displayFormat('d/m/Y H:i'), ->native(false),
              ->seconds(false)
    Field: remarque
      Component: Filament\Forms\Components\Textarea
      Docs: https://filamentphp.com/docs/5.x/forms/textarea
      Validation: nullable
      Config: ->rows(3), ->columnSpanFull()

  Table:
    DefaultSort: jour asc

    Column: jour
      Component: Filament\Tables\Columns\TextColumn
      Config: ->dateTime('d/m/Y H:i'), ->sortable(), ->label('Date')
    Column: remarque
      Component: Filament\Tables\Columns\TextColumn
      Config: ->limit(80), ->wrap(), ->placeholder('—')
```

### 7.5 RelationManager: MembresRelationManager (on CoursResource)

```
RelationManager: MembresRelationManager
  Command: php artisan make:filament-relation-manager CoursResource membres nom --attach --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Cours\RelationManagers\MembresRelationManager
  Relationship: membres (belongsToMany Membre via inscription)
  Title attribute: nom
  Can attach: yes      (existing membres)
  Can create: no       (use MembreResource to create)
  Can edit: no         (use MembreResource to edit)
  Can detach: yes

  Table:
    Column: nom
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable()
    Column: prenom
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable(), ->label('Prénom')
    Column: civilite
      Component: Filament\Tables\Columns\TextColumn
      Config: ->badge(), ->toggleable()
    Column: email
      Component: Filament\Tables\Columns\TextColumn
      Config: ->copyable(), ->toggleable()
    Column: enabled
      Component: Filament\Tables\Columns\IconColumn
      Config: ->boolean(), ->label('Actif')
```

### 7.6 RelationManager: CoursRelationManager (on MembreResource)

```
RelationManager: CoursRelationManager
  Command: php artisan make:filament-relation-manager MembreResource cours nom --attach --no-interaction
  Location: AcMarche\ActivityManager\Filament\Resources\Membres\RelationManagers\CoursRelationManager
  Relationship: cours (belongsToMany Cours via inscription)
  Title attribute: nom
  Can attach: yes
  Can create: no
  Can edit: no
  Can detach: yes

  Table:
    Column: nom
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable(), ->limit(80), ->wrap()
    Column: activite.nom
      Component: Filament\Tables\Columns\TextColumn
      Config: ->label('Activité'), ->badge(), ->toggleable()
    Column: date_debut
      Component: Filament\Tables\Columns\TextColumn
      Config: ->date('d/m/Y'), ->sortable(), ->label('Début')
    Column: date_fin
      Component: Filament\Tables\Columns\TextColumn
      Config: ->date('d/m/Y'), ->sortable(), ->label('Fin'),
              ->placeholder('—')
```

---

## 8. Tests

Location: `tests/Feature/ActivityManager/`. Use Pest 4 + Filament Livewire
helpers (`pestphp/pest-plugin-livewire` is required in root composer).

For each test file:
- `actingAs(User::factory()->create()->assignRole(RolesEnum::ROLE_MDA_ADMIN->value));`
  unless asserting a denial.

### 8.1 ActiviteResourceTest

```
Authorization:
  - guest cannot access list page (assertForbidden / assertRedirect)
  - user without ROLE_MDA_ADMIN cannot access list page
  - user with ROLE_MDA_ADMIN can access all pages and actions

Validation (use dataset pattern):
  - nom is required
  - nom max 150 characters

Component Config:
  - cours_count column uses ->counts('cours')
  - default sort is nom asc
```

### 8.2 CoursResourceTest

```
Authorization:
  - guest cannot access list page
  - user without ROLE_MDA_ADMIN cannot access list page
  - user with ROLE_MDA_ADMIN can access all pages and actions

Validation (use dataset pattern):
  - nom is required
  - nom max 200 characters
  - date_debut is required and must be a valid date
  - date_fin (when set) must be after_or_equal date_debut
  - activite_id (when set) must exist in activite.id

Component Config:
  - activite select uses ->relationship('activite', 'nom')
  - default sort is date_debut desc
  - membres_count column uses ->counts('membres')
  - dates_cours_count column uses ->counts('datesCours')

Filters:
  - activite_id filter restricts to chosen activité
  - en_cours=true returns cours with date_debut <= today AND
    (date_fin null OR date_fin >= today)
```

### 8.3 MembreResourceTest

```
Authorization:
  - same shape as ActiviteResource

Validation (use dataset pattern):
  - nom is required, max 50
  - prenom is required, max 50
  - email (when set) must be a valid email, max 50
  - codepostal (when set) must be integer

Component Config:
  - civilite uses CiviliteEnum (Madame / Monsieur)
  - enabled defaults to true
  - default sort is nom asc

Filters:
  - civilite filter restricts to chosen civilité
  - enabled ternary filter returns matching rows
  - localite filter uses LIKE %v% matching
```

### 8.4 RelationManager tests

```
ActiviteResource::CoursRelationManager:
  - renders with the activité's cours
  - creating a cours auto-sets activite_id to the owner record
  - deleting a cours removes it from the activité

CoursResource::DatesCoursRelationManager:
  - renders with the cours' séances
  - creating a séance auto-sets cours_id
  - deleting a séance removes it

CoursResource::MembresRelationManager:
  - renders with the cours' membres
  - attach action creates an inscription row (cours_id + membre_id)
  - detach action removes the inscription row
  - attaching the same membre twice violates the unique
    (membre_id, cours_id) constraint

MembreResource::CoursRelationManager:
  - renders with the membre's cours
  - attach action creates an inscription row
  - detach action removes the inscription row
```

### 8.5 Policy tests

Plain feature tests (no Livewire) for `Gate::allows('create', Activite::class)`
and friends — one test per role × ability matrix (admin, role holder,
stranger × viewAny/view/create/update/delete).

---

## 9. Navigation & UX

- All three resources share the navigation group `'Activités'`.
- Heroicon namespace: `Filament\Support\Icons\Heroicon`.
- Panel brand: `'Maison des Aînés'`, color `Color::Amber`.
- Edit/Create redirect to the View page (already set in the panel provider).

---

## 10. Checklist (must pass before considering plan implemented)

- [ ] Module loads via `composer update acmarche/activity-manager`
- [ ] `php artisan migrate --database=maria-activity-manager` succeeds
- [ ] `/activity-manager` panel renders for an authenticated user with
       `ROLE_MDA_ADMIN`
- [ ] `/activity-manager` panel rejects users with no `ROLE_MDA_ADMIN`
- [ ] All three resources list, create, edit, view, delete in the browser
- [ ] DatesCours RelationManager on Cours allows adding/removing séances
- [ ] Membres RelationManager on Cours allows attaching/detaching existing
       membres (and respects the unique (membre_id, cours_id) constraint)
- [ ] Cours RelationManager on Membre mirrors the attach/detach behavior
- [ ] Tests under `tests/Feature/ActivityManager/` pass:
       `php artisan test --compact --filter=ActivityManager`
- [ ] `vendor/bin/pint --dirty --format agent` produces no changes
- [ ] No new top-level folders outside `modules/ActivityManager/`
