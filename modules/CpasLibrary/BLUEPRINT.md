# CpasLibrary Module — Filament v5 Implementation Blueprint

> Spec for an implementing agent. Follow the patterns of `modules/Conseil`
> (composer.json, ServiceProvider, PanelProvider, RolesEnum, Policies). All
> components below include full namespaces and docs URLs. The implementing
> agent must NOT make decisions outside what is specified here; ask the user
> first.

Source schema: `data/dumpsql/library.sql` (tables `categorie`, `fiche`, `tag`,
`fiche_tag`, plus untouched legacy `Preference`, `document`).

Module characteristics:
- Namespace: `AcMarche\CpasLibrary`
- Composer package: `acmarche/cpas-library`
- DB connection: `maria-cpas-library` (own MySQL/MariaDB database)
- Filament panel id: `cpas-library-panel`, path: `cpas-library`
- Roles: `ROLE_LIBRARY_ADMIN`, `ROLE_LIBRARY`

---

## 0. Setup Commands

Run from project root **in order**:

```bash
# Register module in main composer.json (manual edit, see Section 1)
composer update acmarche/cpas-library

# Migrations
php artisan migrate --database=maria-cpas-library --path=modules/CpasLibrary/database/migrations

# Filament scaffolds (run inside main app, then move files into module — see Section 4)
php artisan make:filament-resource Categorie --view --generate --no-interaction
php artisan make:filament-resource Fiche --view --generate --no-interaction
php artisan make:filament-resource Tag --view --generate --no-interaction
php artisan make:filament-relation-manager FicheResource tags name --attach --no-interaction
php artisan make:filament-relation-manager CategorieResource fiches name --associate --no-interaction
php artisan make:filament-relation-manager CategorieResource children name --associate --no-interaction
```

Move generated files from `app/Filament/Resources/*` into
`modules/CpasLibrary/src/Filament/Resources/*` and update namespaces from
`App\Filament\Resources\…` to `AcMarche\CpasLibrary\Filament\Resources\…`.

---

## 1. Module Skeleton

Create the same directory layout as `modules/Conseil`:

```
modules/CpasLibrary/
├── composer.json
├── config/
│   ├── cpas-library.php
│   └── database.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
└── src/
    ├── Enums/RolesEnum.php
    ├── Filament/Resources/
    │   ├── Categories/
    │   ├── Fiches/
    │   └── Tags/
    ├── Models/
    ├── Policies/
    └── Providers/
        ├── CpasLibraryServiceProvider.php
        └── Filament/CpasLibraryPanelProvider.php
```

### 1.1 `modules/CpasLibrary/composer.json`

Mirror `modules/Conseil/composer.json` exactly, with:
- `"name": "acmarche/cpas-library"`
- PSR-4 root: `"AcMarche\\CpasLibrary\\": "src/"`
- Factories: `"AcMarche\\CpasLibrary\\Database\\Factories\\": "database/factories/"`
- Seeders: `"AcMarche\\CpasLibrary\\Database\\Seeders\\": "database/seeders/"`
- Provider: `AcMarche\CpasLibrary\Providers\CpasLibraryServiceProvider`

### 1.2 Register in root `/var/www/intranet-laravel/composer.json`

Add to the `repositories` array:
```json
{ "type": "path", "url": "./modules/CpasLibrary" }
```
Add to `require`: `"acmarche/cpas-library": "*@dev"`.

### 1.3 Register Filament panel

Add `AcMarche\CpasLibrary\Providers\Filament\CpasLibraryPanelProvider::class`
to `bootstrap/providers.php` (follow how Conseil's panel is registered).

### 1.4 `config/database.php`

Mirror `modules/Conseil/config/database.php` with:
- `'connection' => 'maria-cpas-library'`
- Connection key `maria-cpas-library`
- Env vars: `DB_CPAS_LIBRARY_DRIVER`, `DB_CPAS_LIBRARY_HOST`, `DB_CPAS_LIBRARY_PORT`,
  `DB_CPAS_LIBRARY_DATABASE` (default: `library`), `DB_CPAS_LIBRARY_USERNAME`,
  `DB_CPAS_LIBRARY_PASSWORD`, `DB_CPAS_LIBRARY_SOCKET`.

### 1.5 `config/cpas-library.php`
```php
<?php
declare(strict_types=1);
return [];
```

### 1.6 ServiceProvider

`src/Providers/CpasLibraryServiceProvider.php` — exact copy of
`ConseilServiceProvider` with:
- Namespace `AcMarche\CpasLibrary\Providers`
- Class name `CpasLibraryServiceProvider`
- `public static int $module_id = 26;` (next free id after Conseil=25; ask user
  if a registry exists before assigning)
- `moduleName()` returns `'cpas-library'`

### 1.7 Panel Provider

`src/Providers/Filament/CpasLibraryPanelProvider.php` — copy
`ConseilPanelProvider` and change:
- Class: `CpasLibraryPanelProvider`
- `->id('cpas-library-panel')`
- `->path('cpas-library')`
- `->brandName('Bibliothèque CPAS')`
- `->colors(['primary' => Color::Emerald])`
- Discovery namespaces switched to `AcMarche\\CpasLibrary\\Filament\\…`

---

## 2. Enums

### 2.1 RolesEnum

Location: `src/Enums/RolesEnum.php`
```php
<?php
declare(strict_types=1);
namespace AcMarche\CpasLibrary\Enums;

enum RolesEnum: string
{
    case ROLE_LIBRARY_ADMIN = 'ROLE_LIBRARY_ADMIN';
    case ROLE_LIBRARY = 'ROLE_LIBRARY';
}
```

### 2.2 FicheTypeEnum

Source column: `fiche.type` (varchar). Values observed in data: `default`.
Treat as a closed enum with a single case for now; expand later if needed.

Location: `src/Enums/FicheTypeEnum.php`

```
Enum: FicheTypeEnum
  Backing: string
  Implements: Filament\Support\Contracts\HasLabel
  Cases:
    - DEFAULT: value 'default', label 'Standard'
```

---

## 3. Models

> **Important**: target DB uses Symfony Doctrine-style column names
> (`createdAt`, `updatedAt`, `userAdd`, `mimeType`, `fileName`, `fileSize`,
> `date_promulgation`, …). Keep the existing column names in migrations and
> declare matching `$casts`/`getters` — do NOT rename them. The schema must
> stay 1-for-1 compatible with the legacy `library` database.

### 3.1 Model: Preference (legacy, no resource)

```
Model: Preference
  Connection: maria-cpas-library
  Table: Preference   // exact case-preserving name
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - name: string(255), required
    - value: string(255), required
    - username: string(255), required
  Unique: (username, name)
  Relationships: none
  Traits: none
```

No Filament resource. Keep the table only for compatibility (read-only).

### 3.2 Model: Category

```
Model: Category
  Connection: maria-cpas-library
  Table: categories
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - parent_id: bigint, foreign(categories.id), nullable, onDelete: setNull
    - name: string(255), required
    - description: string(255), nullable
    - slug: string(255), nullable, unique
    - icon: string(255), nullable
    - color: string(255), nullable
    - departments: json, required          // raw json text in legacy
    - public: boolean, required, default:false
    - users: json, nullable
  Casts:
    - departments => array
    - users => array
    - public => boolean
  Relationships:
    - belongsTo: Category via parent_id  (alias: parent)
    - hasMany: Category via parent_id    (alias: children)
    - hasMany: Fiche via category_id      (alias: fiches)
  Traits: none
  Fillable:
    [parent_id, name, description, slug, icon, color, departments, public, users]
```

### 3.3 Model: Tag

```
Model: Tag
  Connection: maria-cpas-library
  Table: tag
  Timestamps: false
  Attributes:
    - id: bigint, primary
    - name: string(255), required
    - slug: string(255), nullable
  Relationships:
    - belongsToMany: Fiche via fiche_tag (foreign: tag_id, related: fiche_id)
  Fillable: [name, slug]
```

### 3.4 Model: Fiche

```
Model: Fiche
  Connection: maria-cpas-library
  Table: fiche
  Timestamps: false                     // use legacy createdAt/updatedAt manually
  Attributes:
    - id: bigint, primary
    - category_id: bigint, foreign(categories.id), nullable, onDelete: setNull
    - type: string(255), nullable
    - source: string(255), nullable
    - date_promulgation: date, nullable
    - date_publication: date, nullable
    - name: string(255), required
    - description: longtext, nullable
    - userAdd: string(255), required
    - mimeType: string(255), nullable
    - createdAt: datetime, nullable
    - updatedAt: datetime, nullable
    - fileName: string(190), nullable
    - fileSize: integer, nullable
    - slug: string(255), nullable
    - date_rappel: date, nullable
    - type_document: string(255), nullable
    - date_begin: date, nullable
    - date_end: date, nullable
  Indexes:
    - KEY (category_id)
  Casts:
    - date_promulgation => date
    - date_publication => date
    - date_rappel => date
    - date_begin => date
    - date_end => date
    - createdAt => datetime
    - updatedAt => datetime
    - fileSize => integer
  Relationships:
    - belongsTo: Category via category_id   (alias: category)
    - belongsToMany: Tag via fiche_tag (foreign: fiche_id, related: tag_id)  (alias: tags)
  Fillable:
    [category_id, type, source, date_promulgation, date_publication, name,
     description, userAdd, mimeType, createdAt, updatedAt, fileName, fileSize,
     slug, date_rappel, type_document, date_begin, date_end]
  Boot:
    - on creating: set createdAt = now(), updatedAt = now(), userAdd = auth()->user()->username
    - on updating: set updatedAt = now()
    - on creating/updating: if slug empty, set slug = Str::slug($name).'-'.$id (regenerate after first save)
```

### 3.5 Pivot table: fiche_tag

Composite primary (fiche_id, tag_id). Both FKs cascade on delete. Defined in
the `Fiche.tags()` `belongsToMany('fiche_tag', 'fiche_id', 'tag_id')`. No
dedicated model required.

---

## 4. Migrations

All migrations declare `protected $connection = 'maria-cpas-library';` and
wrap creation in `if (Schema::connection(...)->hasTable(...)) return;` —
mirror `modules/Conseil/database/migrations/2026_05_15_100000_create_groupes_table.php`.

Order (timestamps):

1. `2026_05_16_120000_create_categorie_table.php`
   - Columns per Model 3.2, but defer FK on `parent_id` to a later step.
   - `$table->json('departments');` and `$table->json('users')->nullable();`
2. `2026_05_16_120001_create_tag_table.php` — per Model 3.3.
3. `2026_05_16_120002_create_fiche_table.php` — per Model 3.4. Use the legacy
   column names exactly (camelCase preserved). FK `category_id` references
   `categories.id` with `onDelete: setNull`.
4. `2026_05_16_120003_create_fiche_tag_table.php`:
   - `$table->foreignId('fiche_id')->constrained('fiches')->cascadeOnDelete();`
   - `$table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();`
   - `$table->primary(['fiche_id', 'tag_id']);`
5. `2026_05_16_120004_add_parent_fk_to_categorie_table.php` — add self-FK
   `parent_id -> categories.id` (setNull on delete).
6. `2026_05_16_120005_create_preference_table.php`:
   - Columns per Model 3.1; `$table->unique(['username', 'name']);`
7. `2026_05_16_120006_create_document_table.php` — legacy untouched table:
   - `id`, `name string(255)`, `createdAt datetime nullable`, `updatedAt datetime nullable`.

---

## 5. Factories & Seeders

### 5.1 Factories (under `database/factories/`)

- `CategoryFactory` — `name => fake()->words(3, true)`, `slug => Str::slug($name).'-'.uniqid()`, `departments => [DepartmentEnum::CPAS->value]`, `public => false`.
- `TagFactory` — `name => fake()->unique()->word()`, `slug => Str::slug($name)`.
- `FicheFactory` — `name`, `userAdd => fake()->userName()`, `createdAt`/`updatedAt => now()`, `category_id => CategoryFactory::new()`, `type => 'default'`.

### 5.2 Seeders (under `database/seeders/`)

- `CpasLibrarySeeder` — registered manually; left empty by default. Used only
  for tests/dev.

---

## 6. Policies

Location: `src/Policies/`. Mirror `modules/Conseil/src/Policies/GroupePolicy.php`.
Three policies (one per resource model):

- `CategoryPolicy`
- `FichePolicy`
- `TagPolicy`

All share the same role gating logic (factor it into a `protected function
hasRole(User $user)` method in each policy — match Conseil's style, no shared
trait).

```
Policy: CategoryPolicy
  Location: AcMarche\CpasLibrary\Policies\CategoryPolicy
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview#authorization

  Abilities (all delegate to hasRole($user) unless noted):
    viewAny:  hasRole — true for ROLE_LIBRARY or ROLE_LIBRARY_ADMIN
    view:     hasRole
    create:   isAdmin (only ROLE_LIBRARY_ADMIN or app administrator)
    update:   isAdmin
    delete:   isAdmin
    restore:  false
    forceDelete: false

  Helper methods:
    hasRole(User $user): bool
      - $user->isAdministrator() => true
      - $user->hasOneOfThisRoles([RolesEnum::ROLE_LIBRARY_ADMIN->value,
                                  RolesEnum::ROLE_LIBRARY->value]) => true
      - else false

    isAdmin(User $user): bool
      - $user->isAdministrator() => true
      - $user->hasOneOfThisRoles([RolesEnum::ROLE_LIBRARY_ADMIN->value]) => true
      - else false
```

Same shape for `TagPolicy`.

```
Policy: FichePolicy
  Location: AcMarche\CpasLibrary\Policies\FichePolicy

  Abilities:
    viewAny:  hasRole
    view:     hasRole
    create:   hasRole                       // any library user can add fiches
    update:   isAdmin OR (hasRole AND $fiche->userAdd === $user->username)
    delete:   isAdmin OR (hasRole AND $fiche->userAdd === $user->username)
    restore:  false
    forceDelete: false
```

Register policies via `AuthServiceProvider`-style binding inside
`CpasLibraryServiceProvider::boot()` — use `Gate::policy(Model::class, Policy::class)`
(match the registration style used in other modules; if other modules use
auto-discovery, follow that).

---

## 7. Resources

### 7.1 Resource: CategorieResource

```
Resource: CategorieResource
  Command: php artisan make:filament-resource Categorie --view --generate --no-interaction
  Location: AcMarche\CpasLibrary\Filament\Resources\Categories\CategorieResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: name
  GloballySearchableAttributes: [name, slug]

  Navigation:
    Group: Bibliothèque
    Icon: Heroicon::OutlinedFolder
    Sort: 1

  Form:
    Columns: 2

    Field: name
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: required, max:255
      Config: ->required(), ->maxLength(255), ->live(onBlur: true),
              ->afterStateUpdated(fn (Set $set, ?string $state) =>
                $set('slug', Str::slug($state ?? '')))
      Imports: Filament\Schemas\Components\Utilities\Set, Illuminate\Support\Str

    Field: slug
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: nullable, max:255, unique:categorie,slug,{{record}}
      Config: ->maxLength(255), ->unique(ignoreRecord: true)

    Field: parent_id
      Component: Filament\Forms\Components\Select
      Docs: https://filamentphp.com/docs/5.x/forms/select
      Validation: nullable
      Config: ->relationship('parent', 'name', fn ($query, ?Category $record) =>
                $record ? $query->where('id', '!=', $record->id) : $query),
              ->searchable(), ->preload(), ->label('Catégorie parente')

    Field: description
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: nullable, max:255
      Config: ->maxLength(255), ->columnSpanFull()

    Field: icon
      Component: Filament\Forms\Components\Select
      Docs: https://filamentphp.com/docs/5.x/forms/select
      Validation: nullable
      Config: searchable Heroicon picker — ->native(false), ->searchable(), ->allowHtml(),
        ->getSearchResultsUsing() scans the installed blade-heroicons outline set and
        returns `heroicon-o-*` names with a rendered SVG preview label,
        ->getOptionLabelUsing() previews the saved value. Stores a Filament-compatible
        `heroicon-o-*` string.

    Field: color
      Component: Filament\Forms\Components\ColorPicker
      Docs: https://filamentphp.com/docs/5.x/forms/color-picker
      Validation: nullable
      Config: ->hex()

    Field: departments
      Component: Filament\Forms\Components\CheckboxList
      Docs: https://filamentphp.com/docs/5.x/forms/checkbox-list
      Validation: required, array
      Config: ->options(\AcMarche\App\Enums\DepartmentEnum::class),
              ->columns(2), ->required()

    Field: users
      Component: Filament\Forms\Components\TagsInput
      Docs: https://filamentphp.com/docs/5.x/forms/tags-input
      Validation: nullable, array
      Config: ->helperText('Usernames autorisés (laisser vide pour tous)')

    Field: public
      Component: Filament\Forms\Components\Toggle
      Docs: https://filamentphp.com/docs/5.x/forms/toggle
      Validation: required, boolean
      Config: ->default(false)

  Table:
    Column: id
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->sortable(), ->toggleable(isToggledHiddenByDefault: true)

    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable()

    Column: parent.name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Parente'), ->placeholder('—'), ->toggleable()

    Column: icon
      Component: Filament\Tables\Columns\IconColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/icon
      Config: ->icon(fn (string $state): string => $state), ->toggleable()

    Column: color
      Component: Filament\Tables\Columns\ColorColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/color
      Config: ->toggleable()

    Column: public
      Component: Filament\Tables\Columns\IconColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/icon
      Config: ->boolean()

    Column: fiches_count
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->counts('fiches'), ->label('Fiches'), ->sortable()

    Filter: parent_id
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->relationship('parent', 'name'), ->searchable(), ->preload(),
              ->label('Catégorie parente')

    Filter: public
      Component: Filament\Tables\Filters\TernaryFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/ternary
      Config: ->label('Public')

  Infolist:
    Columns: 2
    Entry: name
      Component: Filament\Infolists\Components\TextEntry
      Docs: https://filamentphp.com/docs/5.x/infolists/text-entry
    Entry: parent.name
      Component: Filament\Infolists\Components\TextEntry
      Config: ->label('Catégorie parente'), ->placeholder('—')
    Entry: slug
      Component: Filament\Infolists\Components\TextEntry
    Entry: description
      Component: Filament\Infolists\Components\TextEntry
      Config: ->columnSpanFull()
    Entry: icon
      Component: Filament\Infolists\Components\IconEntry
      Config: ->icon(fn (string $state): string => $state)
    Entry: color
      Component: Filament\Infolists\Components\ColorEntry
    Entry: departments
      Component: Filament\Infolists\Components\TextEntry
      Config: ->badge(), ->separator(',')
    Entry: public
      Component: Filament\Infolists\Components\IconEntry
      Config: ->boolean()

  RelationManagers:
    - FichesRelationManager  (see Section 7.4)
    - ChildrenRelationManager (see Section 7.5)

  Authorization: see CategoryPolicy (Section 6)
```

### 7.2 Resource: FicheResource

```
Resource: FicheResource
  Command: php artisan make:filament-resource Fiche --view --generate --no-interaction
  Location: AcMarche\CpasLibrary\Filament\Resources\Fiches\FicheResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: name
  GloballySearchableAttributes: [name, slug, description]

  Navigation:
    Group: Bibliothèque
    Icon: Heroicon::OutlinedDocumentText
    Sort: 2

  Form:
    Columns: 2

    Section: Identification (ColumnSpan: full)
      Columns: 2

      Field: name
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: required, max:255
        Config: ->required(), ->maxLength(255), ->columnSpanFull(),
                ->live(onBlur: true),
                ->afterStateUpdated(fn (Set $set, ?string $state) =>
                  $set('slug', Str::slug($state ?? '')))
        Imports: Filament\Schemas\Components\Utilities\Set, Illuminate\Support\Str

      Field: slug
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:255
        Config: ->maxLength(255)

      Field: category_id
        Component: Filament\Forms\Components\Select
        Docs: https://filamentphp.com/docs/5.x/forms/select
        Validation: nullable, exists:categorie,id
        Config: ->relationship('category', 'name'), ->searchable(), ->preload(),
                ->label('Catégorie')

      Field: type
        Component: Filament\Forms\Components\Select
        Docs: https://filamentphp.com/docs/5.x/forms/select
        Validation: nullable
        Config: ->options(FicheTypeEnum::class), ->default('default')

      Field: tags
        Component: Filament\Forms\Components\Select
        Docs: https://filamentphp.com/docs/5.x/forms/select
        Validation: nullable, array
        Config: ->relationship('tags', 'name'), ->multiple(), ->preload(),
                ->searchable(), ->createOptionForm([
                  TextInput::make('name')->required()->maxLength(255),
                ])

    Section: Contenu (ColumnSpan: full)
      Columns: 1

      Field: description
        Component: Filament\Forms\Components\RichEditor
        Docs: https://filamentphp.com/docs/5.x/forms/rich-editor
        Validation: nullable
        Config: ->columnSpanFull()

      Field: file_upload  (NOT persisted column — see below)
        Component: Filament\Forms\Components\FileUpload
        Docs: https://filamentphp.com/docs/5.x/forms/file-upload
        Validation: nullable, file, max:51200
        Config: ->disk('cpas-library')                   // configure in filesystems.php
                ->directory('fiches')
                ->visibility('private')
                ->storeFileNamesIn('fileName')
                ->afterStateUpdated(function ($state, Set $set) {
                  if (! $state) return;
                  $set('fileName', $state->getClientOriginalName());
                  $set('fileSize', $state->getSize());
                  $set('mimeType', $state->getMimeType());
                })
                ->dehydrated(true)                       // file is stored
                ->columnSpanFull()
        Note: bind to the three columns (fileName/fileSize/mimeType) via the
              afterStateUpdated callback. The implementing agent should NOT
              add a virtual `file_upload` column to the migration.

      Field: source
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:255
        Config: ->maxLength(255), ->url()

      Field: type_document
        Component: Filament\Forms\Components\TextInput
        Docs: https://filamentphp.com/docs/5.x/forms/text-input
        Validation: nullable, max:255

    Section: Dates (ColumnSpan: full)
      Columns: 3

      Field: date_promulgation
        Component: Filament\Forms\Components\DatePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date

      Field: date_publication
        Component: Filament\Forms\Components\DatePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date

      Field: date_rappel
        Component: Filament\Forms\Components\DatePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date

      Field: date_begin
        Component: Filament\Forms\Components\DatePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date, before_or_equal:date_end

      Field: date_end
        Component: Filament\Forms\Components\DatePicker
        Docs: https://filamentphp.com/docs/5.x/forms/date-time-picker
        Validation: nullable, date, after_or_equal:date_begin

  Table:
    DefaultSort: createdAt desc

    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable(), ->wrap(), ->limit(60)

    Column: category.name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Catégorie'), ->sortable(), ->toggleable()

    Column: tags.name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->badge(), ->separator(','), ->label('Tags'), ->toggleable()

    Column: userAdd
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Auteur'), ->toggleable(), ->searchable()

    Column: mimeType
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Type'), ->toggleable(isToggledHiddenByDefault: true)

    Column: fileSize
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->label('Taille'), ->numeric(), ->suffix(' o'),
              ->toggleable(isToggledHiddenByDefault: true)

    Column: createdAt
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->dateTime(), ->sortable(), ->label('Créé le')

    Column: date_rappel
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->date(), ->sortable(), ->label('Rappel'), ->toggleable()

    Filter: category_id
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->relationship('category', 'name'), ->searchable(), ->preload()

    Filter: tags
      Component: Filament\Tables\Filters\SelectFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/select
      Config: ->relationship('tags', 'name'), ->multiple(), ->preload(),
              ->label('Tags')

    Filter: userAdd
      Component: Filament\Tables\Filters\Filter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/custom
      Config: ->form([TextInput::make('userAdd')])
              ->query(fn (Builder $q, array $data) =>
                $q->when($data['userAdd'] ?? null,
                        fn ($q, $v) => $q->where('userAdd', $v)))

    Filter: has_rappel
      Component: Filament\Tables\Filters\TernaryFilter
      Docs: https://filamentphp.com/docs/5.x/tables/filters/ternary
      Config: ->label('Avec rappel'), ->nullable(),
              ->queries(true:  fn (Builder $q) => $q->whereNotNull('date_rappel'),
                        false: fn (Builder $q) => $q->whereNull('date_rappel'),
                        blank: fn (Builder $q) => $q)

  Actions:
    Action: Download
      Component: Filament\Actions\Action
      Docs: https://filamentphp.com/docs/5.x/actions/overview
      Location: table row + view-page header
      Visibility: when $record->fileName !== null
      Authorization: user passes FichePolicy::view
      Icon: Heroicon::ArrowDownTray
      Behavior:
        - return Storage::disk('cpas-library')->download('fiches/'.$record->fileName, $record->fileName)

  Infolist:
    Columns: 2

    Section: Identification (ColumnSpan: full, Columns: 2)
      Entry: name
        Component: Filament\Infolists\Components\TextEntry
        Config: ->columnSpanFull(), ->weight('bold')
      Entry: category.name
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Catégorie'), ->badge()
      Entry: type
        Component: Filament\Infolists\Components\TextEntry
        Config: ->badge()
      Entry: tags.name
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Tags'), ->badge(), ->separator(',')
      Entry: slug
        Component: Filament\Infolists\Components\TextEntry

    Section: Contenu (ColumnSpan: full, Columns: 1)
      Entry: description
        Component: Filament\Infolists\Components\TextEntry
        Config: ->html(), ->columnSpanFull()
      Entry: source
        Component: Filament\Infolists\Components\TextEntry
        Config: ->url(fn ($state) => $state, true)
      Entry: fileName
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Fichier')
      Entry: mimeType
        Component: Filament\Infolists\Components\TextEntry
      Entry: fileSize
        Component: Filament\Infolists\Components\TextEntry
        Config: ->numeric(), ->suffix(' o')

    Section: Dates (ColumnSpan: full, Columns: 3)
      Entry: createdAt
        Component: Filament\Infolists\Components\TextEntry
        Config: ->dateTime()
      Entry: updatedAt
        Component: Filament\Infolists\Components\TextEntry
        Config: ->dateTime()
      Entry: userAdd
        Component: Filament\Infolists\Components\TextEntry
        Config: ->label('Auteur')
      Entry: date_promulgation
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date()
      Entry: date_publication
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date()
      Entry: date_rappel
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date()
      Entry: date_begin
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date()
      Entry: date_end
        Component: Filament\Infolists\Components\TextEntry
        Config: ->date()

  Authorization: see FichePolicy (Section 6)
```

### 7.3 Resource: TagResource

```
Resource: TagResource
  Command: php artisan make:filament-resource Tag --view --generate --no-interaction
  Location: AcMarche\CpasLibrary\Filament\Resources\Tags\TagResource
  Docs: https://filamentphp.com/docs/5.x/panels/resources/overview
  RecordTitleAttribute: name

  Navigation:
    Group: Bibliothèque
    Icon: Heroicon::OutlinedTag
    Sort: 3

  Form:
    Columns: 2

    Field: name
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: required, max:255, unique:tag,name,{{record}}
      Config: ->required(), ->maxLength(255), ->unique(ignoreRecord: true),
              ->live(onBlur: true),
              ->afterStateUpdated(fn (Set $set, ?string $state) =>
                $set('slug', Str::slug($state ?? '')))
      Imports: Filament\Schemas\Components\Utilities\Set, Illuminate\Support\Str

    Field: slug
      Component: Filament\Forms\Components\TextInput
      Docs: https://filamentphp.com/docs/5.x/forms/text-input
      Validation: nullable, max:255
      Config: ->maxLength(255)

  Table:
    DefaultSort: name asc

    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->searchable(), ->sortable()

    Column: slug
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->toggleable()

    Column: fiches_count
      Component: Filament\Tables\Columns\TextColumn
      Docs: https://filamentphp.com/docs/5.x/tables/columns/text
      Config: ->counts('fiches'), ->label('Fiches'), ->sortable()
      Note: requires `public function fiches(): BelongsToMany { return $this->belongsToMany(Fiche::class, 'fiche_tag'); }` on Tag model

  Infolist:
    Columns: 2
    Entry: name
      Component: Filament\Infolists\Components\TextEntry
    Entry: slug
      Component: Filament\Infolists\Components\TextEntry

  Authorization: TagPolicy (same shape as CategoryPolicy)
```

### 7.4 RelationManager: FichesRelationManager (on CategorieResource)

```
RelationManager: FichesRelationManager
  Command: php artisan make:filament-relation-manager CategorieResource fiches name --associate --no-interaction
  Location: AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\FichesRelationManager
  Relationship: fiches (hasMany Fiche)
  Title attribute: name
  Docs: https://filamentphp.com/docs/5.x/panels/resources/relation-managers
  Can associate: yes      (existing fiches)
  Can create: no          (use FicheResource to create — avoids partial schema)
  Can edit: yes           (link to FicheResource edit)
  Can detach: yes

  Table:
    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable(), ->limit(80)
    Column: userAdd
      Component: Filament\Tables\Columns\TextColumn
      Config: ->label('Auteur'), ->toggleable()
    Column: createdAt
      Component: Filament\Tables\Columns\TextColumn
      Config: ->dateTime(), ->sortable(), ->label('Créé le')
```

### 7.5 RelationManager: ChildrenRelationManager (on CategorieResource)

```
RelationManager: ChildrenRelationManager
  Command: php artisan make:filament-relation-manager CategorieResource children name --associate --no-interaction
  Location: AcMarche\CpasLibrary\Filament\Resources\Categories\RelationManagers\ChildrenRelationManager
  Relationship: children (hasMany Category via parent_id)
  Title attribute: name
  Can create: yes (sets parent_id automatically via relation)
  Can edit: yes
  Can delete: yes

  Form: reuse CategorieResource form (without parent_id field)

  Table:
    Column: name
      Component: Filament\Tables\Columns\TextColumn
      Config: ->searchable(), ->sortable()
    Column: slug
      Component: Filament\Tables\Columns\TextColumn
      Config: ->toggleable()
    Column: public
      Component: Filament\Tables\Columns\IconColumn
      Config: ->boolean()
```

---

## 8. Filesystem disk

Add to `config/filesystems.php` (main app):
```php
'disks' => [
    'cpas-library' => [
        'driver' => 'local',
        'root' => storage_path('app/cpas-library'),
        'visibility' => 'private',
        'throw' => false,
    ],
],
```

---

## 9. Tests

Location: `tests/Feature/CpasLibrary/`. Use Pest 4 + Filament Livewire helpers
(`pestphp/pest-plugin-livewire` is required in root composer).

For each test file:
- `actingAs(User::factory()->create()->assignRole(RolesEnum::ROLE_LIBRARY_ADMIN->value));`
  unless asserting a denial.

### 9.1 CategorieResourceTest

```
Authorization:
  - guest cannot access list page (assertForbidden / assertRedirect)
  - user without ROLE_LIBRARY or ROLE_LIBRARY_ADMIN cannot access list page
  - user with ROLE_LIBRARY can access list and view, but Create/Edit/Delete actions are hidden
  - user with ROLE_LIBRARY_ADMIN can access all pages and actions

Validation (use dataset pattern):
  - name is required
  - name max 255 characters
  - slug max 255 characters
  - slug must be unique
  - departments is required, must be array

Component Config:
  - parent_id select excludes the current record from its options
  - public field defaults to false
  - fiches_count column uses ->counts('fiches')

Filters:
  - parent_id filter limits results to the chosen parent
  - public ternary filter returns matching rows
```

### 9.2 FicheResourceTest

```
Authorization:
  - user with ROLE_LIBRARY can create a fiche
  - user with ROLE_LIBRARY can edit only fiches where userAdd === their username
  - user with ROLE_LIBRARY cannot edit a fiche owned by another username
  - user with ROLE_LIBRARY_ADMIN can edit any fiche
  - user with ROLE_LIBRARY can delete only their own fiches
  - user with ROLE_LIBRARY_ADMIN can delete any fiche

Validation (use dataset pattern):
  - name is required
  - name max 255 characters
  - category_id must exist in categories.id when set
  - date_begin must be before_or_equal date_end
  - date_end must be after_or_equal date_begin

Component Config:
  - name field is live(onBlur: true) and writes a slug via afterStateUpdated
  - file_upload writes fileName, fileSize, mimeType after upload
  - tags select uses ->relationship('tags', 'name')->multiple()
  - default sort is createdAt desc

Actions:
  - Download action is visible only when fileName is set
  - Download action returns a download response

Filters:
  - category_id filter restricts to chosen category
  - tags multi-filter returns fiches having at least one selected tag
  - has_rappel=true returns only rows with date_rappel not null

Lifecycle:
  - creating a fiche sets userAdd to current user's username and createdAt = now()
  - updating a fiche updates updatedAt
  - empty slug is regenerated from name + id after save
```

### 9.3 TagResourceTest

```
Authorization:
  - same shape as CategorieResource

Validation (use dataset pattern):
  - name is required
  - name max 255 characters
  - name must be unique
```

### 9.4 RelationManager tests

```
FichesRelationManager:
  - renders with the category's fiches
  - associate action attaches an existing fiche (sets category_id)
  - detach action sets category_id to null (onDelete: setNull semantics, but use detach=remove association)

ChildrenRelationManager:
  - renders with the category's children
  - creating a child auto-sets parent_id to the owner record
```

### 9.5 Policy tests

Plain feature tests (no Livewire) for `Gate::allows('create', Category::class)`
and friends — one test per role × ability matrix.

---

## 10. Navigation & UX

- All three resources share the navigation group `'Bibliothèque'`.
- Heroicon namespace: `Filament\Support\Icons\Heroicon`.
- Panel brand: `'Bibliothèque CPAS'`, color `Color::Emerald`.
- Edit/Create redirect to the View page (already set in the panel provider).

---

## 11. Checklist (must pass before considering plan implemented)

- [ ] Module loads via `composer update acmarche/cpas-library`
- [ ] `php artisan migrate --database=maria-cpas-library` succeeds
- [ ] `/cpas-library` panel renders for an authenticated user with `ROLE_LIBRARY_ADMIN`
- [ ] `/cpas-library` panel rejects users with no library role
- [ ] All three resources list, create, edit, view, delete in the browser
- [ ] File upload on Fiche stores under `storage/app/cpas-library/fiches/`
       and populates `fileName`, `fileSize`, `mimeType`
- [ ] Tag attach/detach works on Fiche edit page
- [ ] Tests under `tests/Feature/CpasLibrary/` pass:
       `php artisan test --compact --filter=CpasLibrary`
- [ ] `vendor/bin/pint --dirty --format agent` produces no changes
- [ ] No new top-level folders outside `modules/CpasLibrary/`
