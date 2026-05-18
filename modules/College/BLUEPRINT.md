# College Module — Filament v5 Implementation Blueprint

> Spec for an implementing agent. Mirrors the layout of `modules/Conseil` and
> `modules/CpasLibrary` (composer.json, ServiceProvider, PanelProvider,
> RolesEnum, Policies). All file lists below include full namespaces. The
> implementing agent must NOT make decisions outside what is specified here;
> ask the user first.

## Source

`data/dumpsql/college.sql`. Two tables:

- `destinataire` — recipients of agendas/PVs, with four boolean flags
  (`pv_service`, `ordre_service`, `ordre_college`, `pv_college`).
- `notification` — stored notification document (file blob metadata).

Legacy column names are preserved 1‑for‑1 with the dump (e.g. `slugname`,
`updatedAt`). Table names are kept singular as in the dump, matching the
convention already used by `modules/CpasLibrary` (`fiche`, `categorie`).

## Module skeleton

- Package: `acmarche/college`, PSR‑4 root `AcMarche\College\`.
- Service provider: `AcMarche\College\Providers\CollegeServiceProvider`
  (extends `Illuminate\Support\ServiceProvider`, uses
  `AcMarche\App\Traits\ModuleServiceProviderTrait`).
- Panel provider: `AcMarche\College\Providers\Filament\CollegePanelProvider`
  with `id('college-panel')`, `path('college')`, `brandName('Collège')`,
  primary color `Color::Slate`.
- Database connection: `maria-college` (env prefix `DB_COLLEGE_*`).
- Config files: `config/college.php` (empty), `config/database.php`.

## Roles

`AcMarche\College\Enums\RolesEnum` — single case:

- `ROLE_COLLEGE_CONVOCATION = 'ROLE_COLLEGE_CONVOCATION'`

Policies grant access when the user `isAdministrator()` OR
`hasOneOfThisRoles([RolesEnum::ROLE_COLLEGE_CONVOCATION->value])`. Same
methods as `modules/Conseil/src/Policies/DestinatairePolicy.php`:
`viewAny`, `view`, `create`, `update`, `delete` all true for role holders;
`restore`, `forceDelete` false.

## Models

### `AcMarche\College\Models\Destinataire`

- `#[Connection('maria-college')]`, `#[Table(name: 'destinataire')]`,
  `#[UseFactory(DestinataireFactory::class)]`.
- `public $timestamps = false;`.
- Fillable: `slugname`, `nom`, `prenom`, `email`, `pv_service`,
  `ordre_service`, `ordre_college`, `pv_college`.
- Casts: the four flags as `boolean`.
- `booted()`: on `creating` and `updating`, if `slugname` is empty fill it
  with `Str::slug($prenom.' '.$nom, '_')` (matches the legacy format
  `lastname_firstname` lower‑cased with underscores).

### `AcMarche\College\Models\Notification`

- `#[Connection('maria-college')]`, `#[Table(name: 'notification')]`,
  `#[UseFactory(NotificationFactory::class)]`.
- Legacy timestamps: `public const UPDATED_AT = 'updatedAt';` and disable
  `CREATED_AT` (`public const CREATED_AT = null;`).
- Fillable: `file_name`, `mime`, `updatedAt`.
- Casts: `updatedAt` → `datetime`.

## Migrations (connection `maria-college`)

All migrations must wrap in a `Schema::connection('maria-college')->hasTable(...)`
guard like the Conseil migrations, so they're idempotent.

1. `2026_05_18_120000_create_destinataire_table.php`
   - `id()`, `string('slugname', 70)->unique()`, `string('nom', 255)`,
     `string('prenom', 255)`, `string('email', 255)`,
     `boolean('pv_service')->default(false)`,
     `boolean('ordre_service')->default(false)`,
     `boolean('ordre_college')->default(false)`,
     `boolean('pv_college')->default(false)`.

2. `2026_05_18_120001_create_notification_table.php`
   - `id()`, `string('file_name', 255)`, `string('mime', 255)`,
     `dateTime('updatedAt')`.

## Factories

- `DestinataireFactory`: random `nom`/`prenom`, derives `slugname` from
  them, `email` `unique()->safeEmail()`, all four flags random booleans.
- `NotificationFactory`: `file_name` `word().'.pdf'`, `mime`
  `'application/pdf'`, `updatedAt` `now()`.

## Filament Resources

Both resources live under `AcMarche\College\Filament\Resources\…` and use the
same folder layout as Conseil (`Resource.php` + `Pages/` + `Schemas/` +
`Tables/`).

### `DestinataireResource`

- `$model = Destinataire::class`.
- Navigation: `Heroicon::OutlinedUsers`, sort `1`, label `Destinataires`.
- Record title attribute: `nom`. Globally searchable on `nom`, `prenom`,
  `email`.
- Pages: List / Create / View / Edit.

**Form** (`DestinataireForm::configure`): one `Section::make('Identification')`
with a `Grid::make(2)`:
- `TextInput::make('nom')` required, max 255.
- `TextInput::make('prenom')` required, max 255.
- `TextInput::make('email')` required, `->email()`, max 255.
- `TextInput::make('slugname')` max 70, helper "Auto-généré si vide".

A second `Section::make('Convocations')` with a `Grid::make(2)` of four
`Toggle::make()` fields, all defaulting to `false`:
- `pv_service` — label "PV Service".
- `ordre_service` — label "Ordre du jour - Service".
- `ordre_college` — label "Ordre du jour - Collège".
- `pv_college` — label "PV Collège".

**Table** (`DestinatairesTable::configure`):
- Columns: `nom` (sortable/searchable), `prenom` (sortable/searchable),
  `email` (searchable/copyable). Four `IconColumn::make()->boolean()` for
  the flags.
- Filters: `TernaryFilter` for each of the four flags.
- Row actions: `ViewAction`, `EditAction`.
- Toolbar bulk: `DeleteBulkAction` inside a `BulkActionGroup`.

**View page**: infolist with two sections — Identification (`nom`,
`prenom`, `email`, `slugname`) and Convocations (the four flags as
`IconEntry::make()->boolean()`).

### `NotificationResource`

- `$model = Notification::class`.
- Navigation: `Heroicon::OutlinedBell`, sort `2`, label `Notifications`.
- Pages: List / Create / View / Edit.

**Form** (`NotificationForm::configure`): one `Section::make('Document')`:
- `FileUpload::make('file_upload')` on disk `college`, directory
  `notifications`, visibility `private`, `->dehydrated(false)`, in
  `afterStateUpdated` set `file_name` and `mime` from the uploaded file.
- `TextInput::make('file_name')` required, max 255.
- `TextInput::make('mime')` required, max 255.

**Table** (`NotificationsTable::configure`):
- Columns: `id`, `file_name` (searchable), `mime`, `updatedAt`
  (`->dateTime()`, sortable).
- Row actions: View, Edit.
- Bulk: Delete.

**View page**: infolist showing `file_name`, `mime`, `updatedAt`.

## Authorization

- `DestinatairePolicy` and `NotificationPolicy` — both implement the
  Conseil pattern (admin or `ROLE_COLLEGE_CONVOCATION`).
- Resources rely on automatic policy discovery via Filament — no explicit
  `can*` methods overridden.

## Disk

Add a `college` disk in `config/filesystems.php` if it doesn't already
exist. Out of scope for this blueprint — the implementing agent uses
the default `local` disk if `college` isn't registered, and notes the gap.

## Registration

- Add `modules/College` repository entry to root `composer.json`.
- Add `"acmarche/college": "*@dev"` to root requires.
- Add `AcMarche\College\Providers\Filament\CollegePanelProvider::class` to
  `bootstrap/providers.php`.
- Run `composer update acmarche/college` (or
  `composer dump-autoload`).

## Tests (Pest 4, Feature)

- `tests/Feature/Policies/PoliciesTest.php` — viewAny/create on
  Destinataire & Notification for: admin, role holder, stranger.
- `tests/Feature/Filament/Resources/DestinataireResourceTest.php` — list
  shows records, create persists, edit saves, validation errors on
  missing nom/prenom/email.
- `tests/Feature/Filament/Resources/NotificationResourceTest.php` — list
  shows records, create persists.

## Checklist for the implementing agent

- All migrations use `Schema::connection('maria-college')` and the
  `hasTable` guard.
- Models use `#[Connection('maria-college')]` and `#[Table(...)]` for
  singular table names.
- `slugname` is unique (`->unique()` in migration, auto‑generated in
  model `booted()`).
- Notification model overrides `CREATED_AT`/`UPDATED_AT` correctly.
- Policies match the Conseil pattern (admin OR role).
- Panel provider id `college-panel`, path `college`, color `Slate`.
- `vendor/bin/pint --dirty --format agent` after writing code.
