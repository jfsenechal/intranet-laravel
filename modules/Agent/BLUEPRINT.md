# Agent Module — Filament Blueprint

Module that manages **staff profiles** and their **IT provisioning**: mailbox access, external-application access, shared-folder access, hardware requests, telephony setup, audit history and external sharing of the profile.

The core entity is `Profile`. A profile is linked 1:1 to an `App\Models\User` via the shared `username` column (string key, no FK constraint because the `users` table lives on the main DB connection and profiles live on `maria-agent`). Identity fields (`last_name`, `first_name`, `email`) come from the linked user — profiles never duplicate them.

Source dataset: `data/agent.sql` (legacy Symfony application, database `agent`).

- **Namespace:** `AcMarche\Agent`
- **DB connection:** `maria-agent` → database `agent`
- **Filament panel:** `app-panel` (existing `/app`)
- **Navigation group:** `Gestion des agents`
- **Module id (Security):** `40` (matches legacy "Agent" entry)

---

## 1. Commands

```bash
# once (after composer.json update)
composer update acmarche/agent

# migrations (renames legacy tables where they exist)
php artisan migrate --database=maria-agent --path=modules/Agent/database/migrations

# Filament scaffolding (run one by one)
php artisan make:filament-resource Profile \
    --model=AcMarche\\Agent\\Models\\Profile \
    --view --generate --no-interaction
php artisan make:filament-resource ExternalApplication \
    --model=AcMarche\\Agent\\Models\\ExternalApplication \
    --view --generate --no-interaction
php artisan make:filament-resource Folder \
    --model=AcMarche\\Agent\\Models\\Folder \
    --view --generate --no-interaction

# Relation managers (attach to ProfileResource)
php artisan make:filament-relation-manager ProfileResource hardware
php artisan make:filament-relation-manager ProfileResource phone
php artisan make:filament-relation-manager ProfileResource externalApplications name
php artisan make:filament-relation-manager ProfileResource folders name
php artisan make:filament-relation-manager ProfileResource histories name --view
php artisan make:filament-relation-manager ProfileResource shares shared_for
```

After scaffolding, move generated classes from `app/Filament/…` into
`modules/Agent/src/Filament/Resources/…` and update namespaces to
`AcMarche\Agent\Filament\Resources\*` (see existing modules as reference).

---

## 2. Models

All live under `AcMarche\Agent\Models` with `#[Connection('maria-agent')]`.

### `Profile`
Main record for a staff member. Identity comes from the linked `App\Models\User` via `username`.

| Attribute      | Type     | Notes                                    |
| -------------- | -------- | ---------------------------------------- |
| `id`           | bigint   |                                          |
| `username`     | string   | required, unique — links to `users.username` |
| `emails`       | json     | array of shared mailbox addresses        |
| `supervisors`  | json     | array (string list)                      |
| `location`     | string   | nullable (physical office)               |
| `notes`        | longText | nullable                                 |
| `modules`      | json     | array of Security `modules.id` values    |
| `employee_id`  | int      | nullable — FK into `hrm.employees`       |
| `uuid`         | uuid     |                                          |
| `no_mail`      | bool     | "no personal mailbox" flag               |
| `timestamps`   |          |                                          |
| `deleted_at`   | softDel  |                                          |

Relationships:
- `belongsTo App\Models\User` via `username` ↔ `username` (cross-connection string key — no SQL join, Eloquent resolves PHP-side).
- `hasOne ProfileHardware`, `hasOne ProfilePhone`
- `belongsToMany ExternalApplication` (pivot `profile_external_application`)
- `belongsToMany Folder` (pivot `profile_folder`)
- `hasMany History`, `hasMany Share`

Uses `HasUserAdd` booted trait. Deleting a profile cascades through all related tables via FK `ON DELETE CASCADE`.

### `ExternalApplication`
Catalog of third-party applications the profile may need (Civadis, Saphir, eComptes, …).

| Attribute       | Type     |
| --------------- | -------- |
| `id`            | bigint   |
| `name`          | string   |
| `description`   | longText |
| `service_id`    | int      |
| `timestamps`    |          |

`belongsToMany Profile`.

### `Folder`
Self-referential tree of shared network folders (one DFS path per node).

| Attribute      | Type     |
| -------------- | -------- |
| `id`           | bigint   |
| `parent_id`    | fk→folders (cascade) |
| `name`         | string   |
| `description`  | longText |
| `timestamps`   |          |

`belongsTo parent`, `hasMany children`, `belongsToMany Profile`.

### `ProfileHardware` (table `profile_hardware`)
1:1 hardware request per profile.

| Attribute       | Type     |
| --------------- | -------- |
| `profile_id`    | fk→profiles (cascade) |
| `existing_pc`   | string nullable |
| `new_pc`        | string nullable |
| `other`         | longText |
| `vpn`           | bool     |

### `ProfilePhone` (table `profile_phone`)
1:1 telephony request per profile.

| Attribute         | Type     |
| ----------------- | -------- |
| `profile_id`      | fk→profiles (cascade) |
| `existing_number` | string   |
| `new_number`      | bool     |
| `external_number` | bool     |
| `mobile_number`   | string   |

### `History`
Append-only audit trail. Every edit writes one row.

| Attribute     | Type   |
| ------------- | ------ |
| `profile_id`  | fk→profiles (cascade) |
| `name`        | string (attribute name: `email`, `responsables`, `dossier`, …) |
| `old_value`   | json   |
| `new_value`   | json   |
| `username`    | string (author) |
| `timestamps`  |        |

### `Share`
An email address the profile has been "shared for review" with.

| Attribute    | Type   |
| ------------ | ------ |
| `profile_id` | fk→profiles (cascade) |
| `shared_by`  | string (username) |
| `shared_for` | string (email)    |

### Pivots (no model)
`profile_external_application (profile_id, external_application_id)`
`profile_folder (profile_id, folder_id)`

---

## 3. Resources

### 3.1 `ProfileResource`
- **Location:** `modules/Agent/src/Filament/Resources/Profiles/ProfileResource.php`
- **Navigation group:** `Gestion des agents`
- **Navigation icon:** `heroicon-o-identification`
- **Navigation label:** `Profils`
- **Record title attribute:** `username`

**Form** (`Schemas/ProfileForm`):

Use a `Filament\Schemas\Components\Tabs` layout with these tabs:

1. **Identité**
   - `TextInput::make('username')` — required, disabled on edit (set once at create via LDAP picker)
   - `TextInput::make('employee_id')` — numeric, nullable
   - `Toggle::make('no_mail')`
   - `TextInput::make('location')`
   - `Textarea::make('notes')` → columnSpanFull
2. **Accès**
   - `TagsInput::make('emails')` — "Boîtes mails à partager"
   - `TagsInput::make('supervisors')` — "Responsables"
3. **Matériel** — `Fieldset::make('Matériel informatique')->relationship('hardware')` with
   `TextInput existing_pc`, `TextInput new_pc`, `Toggle vpn`, `Textarea other`
4. **Téléphonie** — `Fieldset::make('Téléphonie')->relationship('phone')` with
   `TextInput existing_number`, `TextInput mobile_number`, `Toggle new_number`, `Toggle external_number`

On **Create**, the form is replaced by `UserForm::add($schema)` — a single `Select` of LDAP usernames. The selected username becomes the profile's `username`, which in turn links to the matching `User` row.

**Table** (`Tables/ProfileTables`):

| Column              | Class         | Notes                      |
| ------------------- | ------------- | -------------------------- |
| `username`          | `TextColumn`  | sortable, searchable, badge, copyable |
| `user.last_name`    | `TextColumn`  | display-only (cross-DB — not sortable/searchable) |
| `user.first_name`   | `TextColumn`  | display-only (cross-DB) |
| `location`          | `TextColumn`  | toggleable                 |
| `external_applications_count` | `TextColumn::counts('externalApplications')` | |
| `folders_count`     | `TextColumn::counts('folders')` |       |
| `no_mail`           | `IconColumn`  |                            |
| `updated_at`        | `TextColumn`  | since                      |

Filters:
- `TernaryFilter::make('no_mail')`
- `TrashedFilter::make()`

Row actions: `ViewAction`, `EditAction`, `DeleteAction`.
Header action: `CreateAction`.
Bulk: `DeleteBulkAction`.

**Pages** (`Pages/…`):
- `ListProfiles` (index)
- `CreateProfile` — uses `UserForm::add()` for the LDAP-username picker
- `EditProfile`
- `ViewProfile` — title is `$record->user?->full_name ?? $record->username`

**Relation managers** (attached to `ProfileResource::getRelations()`):
- `ExternalApplicationsRelationManager` (belongsToMany, attach/detach)
- `FoldersRelationManager` (belongsToMany, attach/detach, shows `parent.name`)
- `HistoriesRelationManager` (readonly — diff of `old_value`/`new_value`)
- `SharesRelationManager` (create with `shared_for` email, auto-fill `shared_by` = `auth()->user()->username`)

**Observer** (`AcMarche\Agent\Observers\ProfileObserver`):
On `updated`, iterate `$profile->getChanges()` and write a row to `histories` for each changed attribute. Register via `#[ObservedBy]` on `Profile`.

### 3.2 `ExternalApplicationResource`
- Fields: `name` (required, unique), `description` (Textarea), `service_id`.
- Table: `name`, `profiles_count`, `created_at`.

### 3.3 `FolderResource`
- Tree-like list; `parent.name` column for breadcrumb.
- Fields: `Select parent_id` (self-relation, nullable), `TextInput name`, `Textarea description`.
- Table: `name`, `parent.name`, `profiles_count`.

---

## 4. Authorization

Role-based via the existing `AcMarche\Security` module:

| Action      | Allowed role(s)           |
| ----------- | ------------------------- |
| viewAny     | `module:40 viewer`        |
| view        | viewer                    |
| create      | `module:40 editor`        |
| update      | editor                    |
| delete      | editor                    |
| restore     | `module:40 admin`         |
| forceDelete | admin                     |

Implement policies under `modules/Agent/src/Policies/{Profile,ExternalApplication,Folder}Policy.php` and register via `Gate::policy()` in `AgentServiceProvider::boot()`.

`HistoryPolicy` / `SharePolicy` — read-only for non-admins; history has no update/delete.

---

## 5. Widgets (optional, phase 2)

- `ProfilesOverview` (StatsOverviewWidget):
  - `Total profils` — `Profile::count()`
  - `Sans mailbox` — `Profile::where('no_mail', true)->count()`
  - `Partagés cette semaine` — `Share::where('created_at', '>=', now()->subWeek())->count()`
- `ApplicationsUsageChart` (BarChartWidget): top 10 external applications by assigned profiles.

---

## 6. Tests (Pest)

Feature tests under `modules/Agent/tests/Feature/`:

1. `ProfileResourceTest.php` — list, create, update, soft-delete.
2. `ExternalApplicationResourceTest.php` — CRUD happy path.
3. `FolderResourceTest.php` — CRUD + parent/child.
4. `ProfileRelationsTest.php` — pivots, hasOne upserts.
5. `ShareTest.php` — auto-fill `shared_by`.
6. `Unit/ProfileObserverTest.php` — history rows on update.

Factories required under `modules/Agent/database/factories/`:
`ProfileFactory`, `ExternalApplicationFactory`, `FolderFactory`, `ProfileHardwareFactory`,
`ProfilePhoneFactory`, `HistoryFactory`, `ShareFactory`.

---

## 7. Styling

- **Icons (Heroicons):** Profiles `identification`, External Applications `puzzle-piece`, Folders `folder`, Hardware `computer-desktop`, Phone `phone`, Histories `clock`, Shares `share`.
- **Colors:** `primary` for active, `gray` for soft-deleted rows, `warning` on `no_mail = true`.

---

## 8. Pre-submission checklist

- [ ] `composer update acmarche/agent` done
- [ ] `php artisan migrate --database=maria-agent` clean on a fresh DB **and** on a legacy `agent` DB
- [ ] `AgentServiceProvider` discovered by Laravel package discovery
- [ ] `ProfileResource` appears in `/app` panel under "Gestion des agents"
- [ ] Every list/relation manager eager-loads counts to avoid N+1
- [ ] `vendor/bin/pint --dirty --format agent` clean
- [ ] `php artisan test --compact --filter=Profile` green
