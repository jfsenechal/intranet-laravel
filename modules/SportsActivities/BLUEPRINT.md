# SportsActivities Module — Blueprint Plan

> Filament v5 implementation plan for the `SportsActivities` module (legacy
> `rescam` database). Mirrors the layout of the `Conseil` module.

## Module Identity

| Setting        | Value                                                    |
| -------------- | -------------------------------------------------------- |
| Folder         | `modules/SportsActivities`                               |
| Composer pkg   | `acmarche/sports-activities`                             |
| Namespace      | `AcMarche\SportsActivities\`                             |
| Service provider | `AcMarche\SportsActivities\Providers\SportsActivitiesServiceProvider` |
| Panel provider | `AcMarche\SportsActivities\Providers\Filament\SportsActivitiesPanelProvider` |
| Panel ID       | `sports-activities-panel`                                |
| Panel path     | `/sports-activities`                                     |
| Panel color    | `Color::Emerald`                                         |
| Module ID      | `63`                                                     |
| DB connection  | `maria-rescam` (driver: `mariadb`)                       |
| Env prefix     | `DB_RESCAM_*`                                            |
| Role           | `ROLE_RESCAM` (enum case `AcMarche\SportsActivities\Enums\RolesEnum::ROLE_RESCAM`) |

## Models

All four models target the `maria-rescam` connection, use Laravel-conventional
`snake_case` plural tables, and Laravel-conventional `created_at` / `updated_at`
timestamps (not the legacy `createdAt` / `updatedAt`). The legacy string
`user` column (creator username) is preserved on every table.

### Activite — table `activites`

| Column      | Type             | Notes                                |
| ----------- | ---------------- | ------------------------------------ |
| id          | bigint PK        |                                      |
| nom         | string(255)      | required                             |
| description | longText null    |                                      |
| user        | string(255)      | legacy creator username              |
| archive     | boolean          | default `false`                      |
| timestamps  | timestamps       | `created_at`, `updated_at`           |

Relationships:
- `groupes()`: `HasMany<Groupe>`
- `inscriptions()`: `HasMany<Inscription>`

### Groupe — table `groupes`

| Column       | Type            | Notes                                |
| ------------ | --------------- | ------------------------------------ |
| id           | bigint PK       |                                      |
| activite_id  | FK → activites  | required, cascade on delete (no)     |
| jour         | string(255)     | required (e.g. "Lundi")              |
| heure        | string(255)     | required                             |
| lieux        | string(255)     | required                             |
| age          | string(255)     | required                             |
| prix         | double          | default 0                            |
| description  | longText null   |                                      |
| remarque     | longText null   |                                      |
| user         | string(255)     | legacy creator username              |
| timestamps   | timestamps      |                                      |

Relationships:
- `activite()`: `BelongsTo<Activite>`
- `inscriptions()`: `HasMany<Inscription>`

### Sportif — table `sportifs`

| Column      | Type           | Notes                            |
| ----------- | -------------- | -------------------------------- |
| id          | bigint PK      |                                  |
| nom         | string(255)    | required                         |
| prenom      | string(255)    | required                         |
| ne_le       | date null      |                                  |
| rue         | string(255)    | required                         |
| code_postal | string(255)    | required                         |
| localite    | string(255)    | required                         |
| telephone   | string(255) null |                                |
| gsm         | string(255) null |                                |
| email       | string(255) null |                                |
| remarque    | longText null  |                                  |
| user        | string(255)    | legacy creator username          |
| timestamps  | timestamps     |                                  |

Relationships:
- `inscriptions()`: `HasMany<Inscription>`

### Inscription — table `inscriptions`

| Column       | Type            | Notes                              |
| ------------ | --------------- | ---------------------------------- |
| id           | bigint PK       |                                    |
| activite_id  | FK → activites  | required                           |
| groupe_id    | FK → groupes    | required                           |
| sportif_id   | FK → sportifs   | required                           |
| prix         | double null     |                                    |
| remarque     | longText null   |                                    |
| user         | string(255)     | legacy creator username            |
| timestamps   | timestamps      |                                    |

Unique constraint: `(activite_id, groupe_id, sportif_id)`.

Relationships:
- `activite()`, `groupe()`, `sportif()`: `BelongsTo`

## Filament Resources

Four resources under `src/Filament/Resources/{Activites,Groupes,Sportifs,Inscriptions}`,
mirroring the Conseil layout: `Resource`, `Schemas/*Form`, `Tables/*Table`,
`Pages/{List,Create,Edit,View}`, optional `RelationManagers/`.

### ActiviteResource
- Navigation: `Heroicon::OutlinedSparkles`, sort 1, label "Activités"
- Globally searchable: `nom`
- Form: `nom` (TextInput, required, max 255), `description` (Textarea), `archive` (Toggle, label "Archivée")
- Table columns: `nom` (searchable/sortable), `groupes_count` (counts), `inscriptions_count` (counts), `archive` (IconColumn boolean), `updated_at` (date, toggleable)
- Filter: `SelectFilter` on `archive` with options `Actives` / `Archivées` (default → actives only)
- Relations: `GroupesRelationManager`, `InscriptionsRelationManager`

### GroupeResource
- Navigation: `Heroicon::OutlinedUserGroup`, sort 2, label "Groupes"
- Globally searchable: `jour`, `lieux`, `age`
- Form (Section "Groupe", Grid 2):
  - `activite_id` Select::relationship('activite', 'nom') required searchable preload
  - `jour` TextInput required
  - `heure` TextInput required
  - `lieux` TextInput required
  - `age` TextInput required
  - `prix` TextInput numeric required default 0
  - `description` Textarea columnSpanFull
  - `remarque` Textarea columnSpanFull
- Table columns: `activite.nom`, `jour`, `heure`, `lieux`, `age`, `prix` (money EUR), `inscriptions_count`
- Filter: `SelectFilter::make('activite_id')->relationship('activite','nom')`

### SportifResource
- Navigation: `Heroicon::OutlinedUser`, sort 3, label "Sportifs"
- Globally searchable: `nom`, `prenom`, `email`
- Form (Section "Identité" Grid 2; Section "Adresse" Grid 2; Section "Contact" Grid 2):
  - Identité: `nom`, `prenom` (required), `ne_le` DatePicker
  - Adresse: `rue` columnSpanFull, `code_postal`, `localite`
  - Contact: `telephone`, `gsm`, `email` (email rule)
  - `remarque` Textarea columnSpanFull
- Table columns: `nom`, `prenom`, `localite`, `email`, `gsm`, `inscriptions_count`
- Default sort: `nom asc`

### InscriptionResource
- Navigation: `Heroicon::OutlinedClipboardDocumentCheck`, sort 4, label "Inscriptions"
- Form (Section "Inscription", Grid 2):
  - `sportif_id` Select::relationship('sportif', 'nom') searchable preload required
  - `activite_id` Select::relationship('activite', 'nom') searchable preload required ->live()
  - `groupe_id` Select required, options derived from selected `activite_id` (depends on `Get`); see "Reactive fields" below
  - `prix` TextInput numeric
  - `remarque` Textarea columnSpanFull
- Table columns: `sportif.nom`, `sportif.prenom`, `activite.nom`, `groupe.jour`, `groupe.lieux`, `prix` (money EUR), `created_at` date
- Filters: SelectFilter on `activite_id`, on `groupe_id`

#### Reactive fields (Inscription)

Imports: `Filament\Schemas\Components\Utilities\Get`, `Filament\Schemas\Components\Utilities\Set`.

```php
Select::make('activite_id')
    ->relationship('activite', 'nom')
    ->searchable()
    ->preload()
    ->required()
    ->live()
    ->afterStateUpdated(fn (Set $set) => $set('groupe_id', null)),

Select::make('groupe_id')
    ->label('Groupe')
    ->required()
    ->options(fn (Get $get): array => $get('activite_id')
        ? Groupe::query()
            ->where('activite_id', $get('activite_id'))
            ->get()
            ->mapWithKeys(fn ($g) => [$g->id => "{$g->jour} — {$g->heure} — {$g->lieux}"])
            ->toArray()
        : [])
    ->searchable(),
```

## Policies

One policy per model under `src/Policies/`. Authorize when the user is admin
**or** has `ROLE_RESCAM` (mirror `Conseil\Policies\GroupePolicy` exactly,
swapping the role constant). `restore()` and `forceDelete()` return `false`.

## Pages

Pages follow the Conseil pattern verbatim:
- `List{Plural}`: extends `ListRecords`, adds `CreateAction` with French label
- `Create{Singular}`: extends `CreateRecord`, French title
- `Edit{Singular}`: extends `EditRecord`, header has `ViewAction`
- `View{Singular}`: extends `ViewRecord`, infolist with key fields in `Section`, header has `EditAction` + `DeleteAction`

## Providers / Wiring

- `SportsActivitiesServiceProvider` uses `ModuleServiceProviderTrait`, `moduleName()` returns `"sports-activities"`, `$module_id = 63`.
- `SportsActivitiesPanelProvider` mirrors `ConseilPanelProvider`: panel id, path, color `Emerald`, `viteTheme`, `unsavedChangesAlerts`, `resourceCreatePageRedirect('view')`, `resourceEditPageRedirect('view')`, `databaseNotifications`, `discoverResources/Pages/Widgets`.
- `composer.json`: same shape as `Conseil/composer.json` with the new package name and PSR-4 namespaces.
- Root `composer.json`: add path repo `./modules/SportsActivities` and `"acmarche/sports-activities": "*@dev"` requirement.
- `bootstrap/providers.php`: append `AcMarche\SportsActivities\Providers\Filament\SportsActivitiesPanelProvider::class`.
- `.env`: add `DB_RESCAM_HOST/PORT/DATABASE/USERNAME/PASSWORD` keys (database `rescam`, root/homer to match peer modules).

## Tests

Out of scope for this initial scaffold per parity with the Conseil module —
the Conseil module ships without feature tests for its resources, so the
SportsActivities module follows suit. Add Pest feature tests in a follow-up
when test infrastructure for the `maria-rescam` connection is in place.

## Out of scope (deferred)

- Data import from the legacy `rescam` MySQL dump.
- Replacing the legacy `user` string with a proper FK to `App\Models\User`.
- Converting `archive` to SoftDeletes.
- Pest tests for resources/forms/tables.
