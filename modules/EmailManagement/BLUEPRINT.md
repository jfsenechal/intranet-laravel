# Blueprint: Citoyens resource (EmailManagement)

Admin resource for the citizen mailbox accounts stored in the `citoyens` table of
the **`gestemail`** database (structure: `data/citoyens.sql`).

**Decided scope (confirmed with the user):**

- Data lives in the `gestemail` database, which the module's existing
  `maria-email-management` connection already uses
  (`DB_EMAIL_MANAGEMENT_DATABASE=gestemail`). **No new connection.** A migration
  guarded by `hasTable()` describes the table (§3), the same way as the
  `employes` migration.
- Capabilities: **list, view, edit (database only)**. No create, no delete, no
  LDAP write-through.
- Sensitive data: `userPassword`, `legacy_password`, `auth_token` and
  `remember_token` are never exposed (model `$hidden`, never in any schema).
  `employeeNumber`, `recovery_email` and `recovery_phone` are shown on the
  **view page only** (not in the table).

**Environment notes for the implementer:**

- Installed: `filament/filament` v5.10.3, `laravel/framework` v13.38.0.
- Docs check: the Laravel Boost MCP (`search-docs`) was unavailable while this
  plan was written. As a fallback, APIs were checked against the installed source:
  `TextInput::email()` and `::tel()` (`vendor/filament/forms/src/Components/TextInput.php`),
  `copyable()` (`vendor/filament/support/src/Concerns/CanBeCopied.php`), the
  `Illuminate\Database\Eloquent\Attributes\Hidden` attribute (present in Laravel 13),
  and the `make:filament-resource` flags (`--help`). Lifecycle behavior comes from
  the sibling `Employes` resource, which follows the same pattern.
- Mirror the sibling resource
  `modules/EmailManagement/src/Filament/Resources/Employes/**` for structure
  (Resource + `Pages/` + `Schemas/` + `Tables/`), `declare(strict_types=1)`,
  `final` classes and `#[Override]` on overridden static properties.
- Before editing, read `.ai/rules/filament.md`, `.ai/rules/pages.md` and
  `.ai/rules/tests.md` (they match the paths below).

---

## 1. Commands

Run these from `/var/www/intranet-laravel`:

```bash
php artisan make:filament-resource Citoyen \
  --panel=email-management-panel \
  --model-namespace='AcMarche\EmailManagement\Models' \
  --resource-namespace='AcMarche\EmailManagement\Filament\Resources' \
  --view --record-title-attribute=mail --not-embedded --no-interaction
```

Make sure the files end up in
`modules/EmailManagement/src/Filament/Resources/Citoyens/`. If the command writes
them anywhere else, move them there and fix the namespaces. Then:

- **Delete** the generated `Pages/CreateCitoyen.php` (there is no create page).
- Create the model, factory, policy and migration **by hand**, following
  the sibling files (`Models/Employe.php`, `database/factories/EmployeFactory.php`,
  `Policies/EmployePolicy.php`). Do not use `--model`, `--factory` or `--migration`:
  they would generate into `App\`.
- Create the test: `php artisan make:test --pest CitoyenResourceTest`, then
  move it to `modules/EmailManagement/tests/Feature/CitoyenResourceTest.php`.

---

## 2. Database connection

No change. The model uses the existing `maria-email-management` connection
(database `gestemail`). The tests already move that connection to its own
in-memory sqlite database (`DEDICATED_CONNECTIONS` in `tests/TestCase.php`), and
module migrations run there automatically.

---

## 3. Models

### Model: `AcMarche\EmailManagement\Models\Citoyen`

**File:** `modules/EmailManagement/src/Models/Citoyen.php`

Attributes and traits, matching `Employe`:

```php
#[Connection('maria-email-management')]
#[Fillable([
    'givenName',
    'sn',
    'cn',
    'postalAddress',
    'postalCode',
    'l',
    'description',
    'recovery_email',
    'recovery_phone',
])]
#[Hidden([
    'userPassword',
    'legacy_password',
    'auth_token',
    'remember_token',
])]
#[UseFactory(CitoyenFactory::class)]
final class Citoyen extends Model
{
    use HasFactory;
    ...
}
```

Imports: `Illuminate\Database\Eloquent\Attributes\{Connection, Fillable, Hidden, UseFactory}`,
`Illuminate\Database\Eloquent\Factories\HasFactory`, `Illuminate\Database\Eloquent\Model`,
`AcMarche\EmailManagement\Database\Factories\CitoyenFactory`.

Add a PHPDoc on the class: these are the citizen mailbox accounts, whose
directory source of truth is the LDAP `ou=Citoyens`.
Edits here change the database only and are **not** pushed to LDAP or Dovecot.

The table name defaults to `citoyens`, so no `#[Table]` attribute is needed.

**Columns** (from `data/citoyens.sql`; read-only unless listed in `Fillable`):

| Column                      | Type                     | Null | Notes                                       |
| --------------------------- | ------------------------ | ---- | ------------------------------------------- |
| id                          | bigint unsigned, PK, AI  | no   |                                             |
| uid                         | varchar(255), unique     | no   | read-only                                   |
| dn                          | varchar(255), unique     | no   | read-only                                   |
| mail                        | varchar(255), unique     | no   | read-only                                   |
| givenName                   | varchar(255)             | yes  | editable                                    |
| sn                          | varchar(255)             | yes  | editable                                    |
| cn                          | varchar(255)             | yes  | editable                                    |
| l                           | varchar(255)             | yes  | editable (locality)                         |
| postalAddress               | varchar(255)             | yes  | editable                                    |
| employeeNumber              | varchar(255)             | yes  | read-only, view page only                   |
| postalCode                  | varchar(255)             | yes  | editable                                    |
| gosaMailQuota               | double                   | no   | read-only, in Mo                            |
| homeDirectory               | varchar(255)             | no   | read-only                                   |
| gosaMailForwardingAddress   | varchar(255)             | no   | read-only                                   |
| gosaMailAlternateAddress    | varchar(255)             | yes  | read-only                                   |
| userPassword                | varchar(255)             | yes  | **hidden, never shown**                     |
| legacy_password             | varchar(255)             | yes  | **hidden, never shown**                     |
| description                 | text                     | yes  | editable                                    |
| last_connection             | date                     | yes  | read-only                                   |
| created_at / updated_at     | timestamp                | yes  | Eloquent timestamps                         |
| auth_token                  | varchar(255), unique     | yes  | **hidden, never shown**                     |
| remember_token              | varchar(100)             | yes  | **hidden, never shown**                     |
| charter_accepted_at         | timestamp                | yes  | read-only                                   |
| recovery_email              | varchar(255)             | yes  | editable, view page only                    |
| recovery_phone              | varchar(255)             | yes  | editable, view page only                    |
| password_changed_at         | timestamp                | yes  | read-only                                   |

**Casts** (`protected function casts(): array`):

```php
return [
    'gosaMailQuota' => 'float',
    'last_connection' => 'date',
    'charter_accepted_at' => 'datetime',
    'password_changed_at' => 'datetime',
];
```

**Helper:** `public function getFullName(): string` returns
`mb_trim($this->givenName.' '.$this->sn)`, the same as `Employe::getFullName()`.

**Relationships:** none.

### Factory: `AcMarche\EmailManagement\Database\Factories\CitoyenFactory`

**File:** `modules/EmailManagement/database/factories/CitoyenFactory.php`.
Use `#[UseModel(Citoyen::class)]` and `final`, like `EmployeFactory`.

`definition()`:

- `$givenName = fake()->firstName()`, `$sn = fake()->lastName()`
- `uid`: `Str::lower(Str::ascii($givenName).'.'.Str::ascii($sn))`, stripped to
  `[a-z0-9.]`, with `fake()->unique()->numberBetween(1, 9999)` appended
- `dn`: `fn (array $a): string => "uid={$a['uid']},ou=Users,ou=Citoyens,dc=marche,dc=be"`
- `mail`: `fn (array $a): string => "{$a['uid']}@marche.be"`
- `givenName`, `sn`; `cn`: `"{$sn} {$givenName}"`
- `l`: `'Marche-en-Famenne'`; `postalCode`: `'6900'`
- `postalAddress`: `fake()->optional()->streetAddress()`
- `employeeNumber`: `fake()->numerify('###########')`
- `gosaMailQuota`: `150`
- `homeDirectory`: `fn (array $a): string => '/var/spool/dovecot/mail/'.$a['uid'][0].'/'.$a['uid']`
- `gosaMailForwardingAddress`: `fn (array $a): string => "{$a['uid']}@citoyen.marche.be"`
- `userPassword`: `'{SSHA}'.Str::random(32)`; `legacy_password`: `null`
- `auth_token`: `Str::random(64)` (this column is unique)
- `last_connection`: `fake()->optional()->dateTimeBetween('-2 years')`
- `recovery_email`: `null`; `recovery_phone`: `null`

### Migration

**File:**
`modules/EmailManagement/database/migrations/2026_10_27_000000_create_citoyens_table.php`

Follow `0001_01_01_000000_create_employe_table.php` exactly: use
`protected $connection = 'maria-email-management';` and
`Schema::connection('maria-email-management')`, and **guard with
`if (! $schema->hasTable('citoyens'))`**. If `gestemail` already has the table
(imported from the dump), the migration does nothing in production. The tests
use it to build the schema. Reproduce the columns above exactly: `id()`, the
unique `uid`, `dn`, `mail` and `auth_token`, `$table->double('gosaMailQuota')`,
`$table->date('last_connection')->nullable()`, `rememberToken()`, `timestamps()`,
and so on. `down()` drops `citoyens` on the same connection.

---

## 4. Resource

```
Resource: CitoyenResource
  Command: see §1
  Location: modules/EmailManagement/src/Filament/Resources/Citoyens/CitoyenResource.php
  Namespace: AcMarche\EmailManagement\Filament\Resources\Citoyens
  Docs: https://filamentphp.com/docs/5.x/resources/overview
  Panel: email-management-panel (auto-discovered, nothing to register)
  Model: AcMarche\EmailManagement\Models\Citoyen
  RecordTitleAttribute: mail
  Navigation:
    #[Override] protected static ?string $navigationLabel = 'Citoyens'
    #[Override] protected static ?string $modelLabel = 'citoyen'
    #[Override] protected static ?string $pluralModelLabel = 'Citoyens'
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup
    #[Override] protected static ?int $navigationSort = 9   (before CitoyenPage = 10)
  form():     CitoyenForm::configure($schema)
  infolist(): CitoyenInfolist::configure($schema)
  table():    CitoyensTable::configure($table)
  getRelations(): []
  getPages():
    'index' => ListCitoyens::route('/')
    'view'  => ViewCitoyen::route('/{record}')
    'edit'  => EditCitoyen::route('/{record}/edit')
  (No 'create' entry.)
```

### Pages

**`Pages/ListCitoyens.php`** extends `Filament\Resources\Pages\ListRecords`

- `getTitle(): string|Htmlable` returns `$this->getAllTableRecordsCount().' citoyens'`
  (same as `ListEmployes`).
- `getHeaderActions()` returns `[]`. There is no create action.

**`Pages/ViewCitoyen.php`** extends `Filament\Resources\Pages\ViewRecord`

- `getTitle(): string` returns `$this->record->mail`.
- `getHeaderActions()` returns
  `[ EditAction::make()->icon(Heroicon::Pencil) ]` (`Filament\Actions\EditAction`).

**`Pages/EditCitoyen.php`** extends `Filament\Resources\Pages\EditRecord`

- `getTitle(): string` returns `$this->record->mail`.
- `getHeaderActions()` returns `[ ViewAction::make()->icon(Heroicon::Eye) ]`.
  **No `DeleteAction`.** Remove it if the scaffold generated one.
- Add **no** `afterSave()` hook: there is no LDAP write-through.
- After saving, the panel redirects to the view page
  (`resourceEditPageRedirect('view')` is already set).

### Form: `Schemas/CitoyenForm.php`

`final class CitoyenForm { public static function configure(Schema $schema): Schema }`

The form contains **only the 9 fillable fields**. The read-only columns are not
form components at all (not even disabled ones). Filament only hydrates and saves
fields that are in the schema, so `mail`, `uid`, `dn`, the quota and the hashes
can never be submitted.

Layout: the schema's default 2 columns, and **each section uses the default
column count** (1) or `->columns()` exactly like `EmployeForm`. Each `Section` is
direct child of the root schema, matching `EmployeForm`.

```
Section: 'Identité'   (Filament\Schemas\Components\Section, ->columns())
  Field: givenName
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input
    Validation: nullable, max:255
    Config: ->label('Prénom'), ->maxLength(255)
  Field: sn
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input
    Validation: nullable, max:255
    Config: ->label('Nom'), ->maxLength(255)
  Field: cn
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input
    Validation: nullable, max:255
    Config: ->label('Nom complet'), ->maxLength(255), ->columnSpanFull()

Section: 'Adresse'   (->columns())
  Field: postalAddress
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input
    Validation: nullable, max:255
    Config: ->label('Rue'), ->maxLength(255), ->columnSpanFull()
  Field: postalCode
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input
    Validation: nullable, max:255
    Config: ->label('Code postal'), ->maxLength(255)
  Field: l
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input
    Validation: nullable, max:255
    Config: ->label('Localité'), ->maxLength(255)

Section: 'Récupération du compte'   (->columns())
  Field: recovery_email
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input#setting-the-html-input-type
    Validation: nullable, email, max:255
    Config: ->label('Email de récupération'), ->email(), ->maxLength(255)
  Field: recovery_phone
    Component: Filament\Forms\Components\TextInput
    Docs: https://filamentphp.com/docs/5.x/forms/text-input#setting-the-html-input-type
    Validation: nullable, max:255 (the ->tel() regex)
    Config: ->label('Téléphone de récupération'), ->tel(), ->maxLength(255)

Section: 'Divers'
  Field: description
    Component: Filament\Forms\Components\Textarea
    Docs: https://filamentphp.com/docs/5.x/forms/textarea
    Validation: nullable, max:65535
    Config: ->label('Description'), ->rows(3), ->maxLength(65535), ->columnSpanFull()
```

**Edit lifecycle (consistency rule).**

- **Rule:** the database row is the only target. Only the 9 fillable fields are
  ever written.
- **Stages:** the form is hydrated from the record → the admin edits → Filament
  validates with the rules above → `save()` → redirect to the view page, which
  shows the persisted values.
- **Consistency check:** saving with no changes must succeed and leave every
  column unchanged, including `updated_at` (Eloquent skips the save when the
  model isn't dirty). Saving a change must leave the read-only columns
  (`mail`, `uid`, `dn`, `gosaMailQuota`, `homeDirectory`, `userPassword`,
  `auth_token`) byte-identical.
- **Known risk:** legacy rows with an invalid `recovery_email` will fail
  validation until that field is fixed. This is intended. The dump currently has
  only `NULL` values there.

### Infolist: `Schemas/CitoyenInfolist.php`

`final class CitoyenInfolist { public static function configure(Schema $schema): Schema }`

Layout: default 2 schema columns, sections with `->columns()`, the same as
`EmployeInfolist`. All entries use `Filament\Infolists\Components\TextEntry`
(Docs: https://filamentphp.com/docs/5.x/infolists/text-entry). **No entry for
`userPassword`, `legacy_password`, `auth_token` or `remember_token`.**

```
Section: 'Identité'   (Filament\Schemas\Components\Section, ->columns())
  Entry: givenName   ->label('Prénom')->placeholder('-')
  Entry: sn          ->label('Nom')->placeholder('-')
  Entry: cn          ->label('Nom complet')->placeholder('-')
  Entry: uid         ->label('Identifiant')
  Entry: employeeNumber ->label('Numéro national')->placeholder('-')

Section: 'Messagerie'   (->columns())
  Entry: mail                       ->label('Email')->copyable()
  Entry: gosaMailForwardingAddress  ->label('Adresse de transfert')->copyable()
  Entry: gosaMailAlternateAddress   ->label('Adresse alternative')->placeholder('-')
  Entry: gosaMailQuota              ->label('Quota')->numeric()->suffix(' Mo')
  Entry: homeDirectory              ->label('Répertoire')->columnSpanFull()

Section: 'Adresse'   (->columns())
  Entry: postalAddress ->label('Rue')->placeholder('-')->columnSpanFull()
  Entry: postalCode    ->label('Code postal')->placeholder('-')
  Entry: l             ->label('Localité')->placeholder('-')

Section: 'Récupération du compte'   (->columns())
  Entry: recovery_email ->label('Email de récupération')->placeholder('-')->copyable()
  Entry: recovery_phone ->label('Téléphone de récupération')->placeholder('-')

Section: 'Connexion'   (->columns())
  Entry: last_connection      ->label('Dernière connexion')->date()->placeholder('Jamais')
  Entry: password_changed_at  ->label('Mot de passe changé le')->dateTime()->placeholder('Jamais')
  Entry: charter_accepted_at  ->label('Charte acceptée le')->dateTime()->placeholder('Non acceptée')

Section: 'Divers'
  Entry: dn          ->label('DN')->columnSpanFull()
  Entry: description ->label('Description')->placeholder('-')->columnSpanFull()
  Entry: created_at  ->label('Créé le')->dateTime()
  Entry: updated_at  ->label('Modifié le')->dateTime()
```

### Table: `Tables/CitoyensTable.php`

`final class CitoyensTable { public static function configure(Table $table): Table }`

Table config: `->defaultPaginationPageOption(50)`, `->defaultSort('sn', 'asc')`.

**No `employeeNumber`, recovery or hash columns in the table.**

```
Column: sn
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text
  Config: ->label('Nom'), ->sortable(), ->searchable(), ->toggleable()
Column: givenName
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text
  Config: ->label('Prénom'), ->sortable(), ->searchable(), ->toggleable()
Column: mail
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text
  Config: ->label('Email'), ->sortable(), ->searchable(), ->copyable()
Column: uid
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text
  Config: ->label('Identifiant'), ->sortable(), ->searchable(), ->toggleable()
Column: l
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text
  Config: ->label('Localité'), ->sortable(), ->toggleable(isToggledHiddenByDefault: true)
Column: gosaMailQuota
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text#number-formatting
  Config: ->label('Quota (Mo)'), ->numeric(), ->sortable(), ->toggleable(isToggledHiddenByDefault: true)
Column: last_connection
  Component: Filament\Tables\Columns\TextColumn
  Docs: https://filamentphp.com/docs/5.x/tables/columns/text#date-formatting
  Config: ->label('Dernière connexion'), ->date(), ->sortable(), ->toggleable()

Filter: last_connection
  Component: Filament\Tables\Filters\Filter
  Docs: https://filamentphp.com/docs/5.x/tables/filters/custom
  Config: copy the `last_connection` filter from EmployesTable exactly:
    ->label('Dernière connexion')
    ->schema([ DatePicker::make('connected_before')->label('Dernière connexion avant le') ])
      (Filament\Forms\Components\DatePicker)
    ->query(fn (Builder $query, array $data): Builder => $query->when(
        $data['connected_before'],
        fn (Builder $query, string $date): Builder => $query->whereDate('last_connection', '<=', $date)
    ))
    (Illuminate\Database\Eloquent\Builder)
  Rows with a NULL last_connection are excluded when the filter is set (whereDate on NULL is false).

Filter: charter_accepted_at
  Component: Filament\Tables\Filters\TernaryFilter
  Docs: https://filamentphp.com/docs/5.x/tables/filters/ternary#using-a-ternary-filter-with-a-nullable-column
  Config: ->label('Charte acceptée'), ->nullable()

Record actions (->recordActions([...])):
  Action: View
    Component: Filament\Actions\ViewAction
    Docs: https://filamentphp.com/docs/5.x/actions/view
    Location: table row
    Visibility: always (policy view)
    Authorization: CitoyenPolicy::view
    Behavior: opens the view page
  Action: Edit
    Component: Filament\Actions\EditAction
    Docs: https://filamentphp.com/docs/5.x/actions/edit
    Location: table row
    Visibility: always (policy update)
    Authorization: CitoyenPolicy::update
    Behavior: opens the edit page

Toolbar / bulk actions: none (no DeleteBulkAction).
```

---

## 5. Authorization

```
Resource: CitoyenResource
  Policy: AcMarche\EmailManagement\Policies\CitoyenPolicy
  File: modules/EmailManagement/src/Policies/CitoyenPolicy.php
  Docs: https://filamentphp.com/docs/5.x/resources/overview#authorization
  Registration: automatic. Laravel discovers Models\Citoyen → Policies\CitoyenPolicy
    by convention, the same way EmployePolicy is found. No Gate::policy call is needed.
  Uses trait: AcMarche\EmailManagement\Policies\Concerns\EmailManagementAuthorization

  Abilities:
    viewAny(User $user): $this->isEmailAdmin($user)
    view(User $user, Citoyen $citoyen): $this->isEmailAdmin($user)
    update(User $user, Citoyen $citoyen): $this->isEmailAdmin($user)
    create(User $user): false
    delete(User $user, Citoyen $citoyen): false
    deleteAny(User $user): false
    restore(User $user, Citoyen $citoyen): false
    forceDelete(User $user, Citoyen $citoyen): false
```

`isEmailAdmin` passes for `isAdministrator()` users and for users with
`RolesEnum::ROLE_EMAIL_ADMIN`. The panel's `EnsureEmailAdmin` middleware already
blocks other users. The policy is a second layer that also returns false for
create and delete.

---

## 6. Tests

**File:** `modules/EmailManagement/tests/Feature/CitoyenResourceTest.php`

Use the same `beforeEach` as `EmployeResourceTest`: set the panel to
`email-management-panel`, create a `ROLE_EMAIL_ADMIN` role and a non-administrator
user with that role, then `actingAs`. Use `use function Pest\Livewire\livewire;`
and the helpers already used in `EmployeResourceTest` (`loadTable`,
`assertCanSeeTableRecords`, `searchTable`, `filterTable`, `fillForm`,
`assertHasFormErrors`). No new dependency is needed.

```
CitoyenResource:
  Pages render:
    - list page renders and shows 3 factory citoyens
    - view page renders for a citoyen
    - edit page renders for a citoyen

  Sensitive data:
    - view page does NOT show the citoyen's userPassword or auth_token values
      (assertDontSee with the factory's exact strings), but DOES show employeeNumber
    - list page: assertTableColumnDoesNotExist('employeeNumber'),
      assertTableColumnDoesNotExist('recovery_email')
    - Citoyen::factory()->create()->toArray() has no key for userPassword,
      legacy_password, auth_token or remember_token

  Table:
    - search by mail: only the matching citoyen is visible
      (assertCanSeeTableRecords / assertCanNotSeeTableRecords on the others)
    - search by uid: only the matching citoyen is visible
    - last_connection filter: with citoyens at 2026-01-10, 2026-06-01 and NULL,
      filtering with connected_before = 2026-03-01 shows only the 2026-01-10 one
    - charter_accepted_at ternary: true → only the accepted one; false → only the NULL one

  Edit:
    - editing sn, postalCode and recovery_email saves those values (assert with ->refresh())
    - discriminating read-only check: create a citoyen with known mail, uid,
      gosaMailQuota = 250 and userPassword. Then
      fillForm(['sn' => 'Nouveau', 'mail' => 'hack@x.be', 'gosaMailQuota' => 9999])
      ->call('save'). Afterwards sn = 'Nouveau', and mail, uid, gosaMailQuota and
      userPassword equal their original values
    - an unchanged save (call('save') without fillForm) has no form errors and
      leaves updated_at unchanged (freeze time with $this->travelTo before creating;
      travel forward before saving)

  Validation (use dataset pattern on the edit page):
    - recovery_email: 'not-an-email' → error 'email'; 'a@b.be' → no error
    - sn: str_repeat('a', 256) → error 'max'; str_repeat('a', 255) → no error
    - postalCode: str_repeat('1', 256) → error 'max'

  Authorization:
    - a user without ROLE_EMAIL_ADMIN who is not an administrator gets 403 on the
      list page URL (CitoyenResource::getUrl('index'))
    - CitoyenResource::hasPage('create') is false
    - the edit page has no DeleteAction: assertActionDoesNotExist(DeleteAction::class)
    - (new CitoyenPolicy)->delete($admin, $citoyen) and ->create($admin) return false,
      even for an isAdministrator() user
```

Run:

```bash
php vendor/bin/pest modules/EmailManagement/tests/Feature/CitoyenResourceTest.php
php vendor/bin/pint --dirty --format agent
```

Then ask the user to run the full suite: `php artisan test --compact`.

---

## 7. Files touched (summary)

| Action  | Path                                                                                      |
| ------- | ----------------------------------------------------------------------------------------- |
| create  | `modules/EmailManagement/src/Models/Citoyen.php`                                          |
| create  | `modules/EmailManagement/database/factories/CitoyenFactory.php`                           |
| create  | `modules/EmailManagement/database/migrations/2026_10_27_000000_create_citoyens_table.php` |
| create  | `modules/EmailManagement/src/Policies/CitoyenPolicy.php`                                  |
| create  | `modules/EmailManagement/src/Filament/Resources/Citoyens/CitoyenResource.php`             |
| create  | `.../Citoyens/Pages/{ListCitoyens,ViewCitoyen,EditCitoyen}.php`                           |
| create  | `.../Citoyens/Schemas/{CitoyenForm,CitoyenInfolist}.php`                                  |
| create  | `.../Citoyens/Tables/CitoyensTable.php`                                                   |
| create  | `modules/EmailManagement/tests/Feature/CitoyenResourceTest.php`                           |
