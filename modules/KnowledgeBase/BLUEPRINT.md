# KnowledgeBase Module — Filament v5 Implementation Blueprint

> Spec for an implementing agent. Follow the patterns of `modules/Note`
> (composer.json, ServiceProvider, PanelProvider) and `modules/Mileage`
> (RolesEnum, role-based checks). Do NOT make decisions outside this spec; ask
> the user first.

Decisions agreed with the user:

| Topic   | Decision                                                                 |
| ------- | ------------------------------------------------------------------------ |
| Name    | `KnowledgeBase`. Namespace `AcMarche\KnowledgeBase`, package `acmarche/knowledge-base`, config key `knowledge-base`, panel path `/knowledge-base` |
| Storage | Markdown files on a **private** local disk. **No database table.** Pages are created and edited inside Filament. **No versioning**: one current version per page |
| Access  | Users holding `ROLE_KNOWLEDGE_BASE` (read) or `ROLE_KNOWLEDGE_BASE_ADMIN` (read + write), **plus intranet administrators** (`is_administrator` or `ROLE_INTRANET_ADMIN`, same check as `User::canImpersonate()`), who get read + write. Everybody else is denied |
| Secrets | Never stored. Pages point to the password vault. Saving runs a scan that **warns** (it does not block) when content looks like a secret |
| Code    | Syntax highlighting on the server with `phiki/phiki` (new dependency, approved). GitHub light/dark themes follow Filament's dark mode |
| Images  | Images uploaded from the Markdown editor are stored on the **private** disk. They are served only through an authenticated route that checks the read role, and they never go on the public disk |

Verification: APIs were checked with Boost `search-docs` (filament/filament 5.x) and
against installed source (`filament/filament` 5.10.1,
`vendor/filament/filament/src/Pages/Concerns/HasRoutes.php`,
`vendor/filament/forms/src/Components/Concerns/HasFileAttachments.php`).

---

## 0. Content layout (inspired by laravel/docs)

The `laravel/docs` repository is **flat**. Each page is one `{slug}.md` file in the
root, and a single `documentation.md` file defines the sidebar (sections and
order) as a nested Markdown list. Laravel keeps each version in its own git branch.
We copy the flat layout and the `documentation.md` index, with no versions.
Images go in an `images/` folder.

Disk `knowledge-base` → `storage/app/private/knowledge-base/`:

```
storage/app/private/knowledge-base/
├── documentation.md          # sidebar index (sections + order), edited in the panel
├── vpn-forticlient.md        # one page = one file, slug = filename
├── firewall-rules.md
├── backup-restore-veeam.md
├── deploy-laravel-app.md
├── git-workflow.md
└── images/                   # editor uploads, private; named {uuid}.{ext}
    └── 3f1c…-….png
```

`documentation.md` format (same shape as Laravel's file, without `{{version}}`):

```markdown
- ## Réseau
    - [VPN FortiClient](vpn-forticlient)
    - [Règles firewall](firewall-rules)
- ## Serveurs
    - [Sauvegarde & restauration](backup-restore-veeam)
- ## Développement
    - [Déployer une application Laravel](deploy-laravel-app)
    - [Workflow Git](git-workflow)
```

Canonical rules (use them everywhere: routes, create validation, service):

- **Slug rule:** `^[a-z0-9]+(?:-[a-z0-9]+)*$`, max 100 chars. `documentation` is
  reserved and is not a page slug. Every disk path is built **only** as
  `"{$slug}.md"` after this regex passes. This prevents path traversal.
- **Title rule:** the first line matching `^# (.+)$` in the file. If there is none,
  use `Str::headline($slug)`.
- **Section rule:** the `## ` heading under which the slug appears in
  `documentation.md`. If the slug is not listed, use `null` ("Non classé").
- **Image filename rule:** `^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\.(png|jpg|gif|webp)$`.
  Every image path on disk is built **only** as `"images/{$filename}"` after this regex passes.
- The only subfolder is `images/`. `all()` lists root `*.md` files only.

---

## 1. Commands

```bash
# 1. Create module skeleton manually (see §2), then register it in root composer.json
#    "repositories": add { "type": "path", "url": "./modules/KnowledgeBase" }
#    "require": add "acmarche/knowledge-base": "*"
composer update acmarche/knowledge-base

# Syntax highlighting (approved by the user). Add "phiki/phiki": "^2.0" to the
# module composer.json "require" too.
composer require phiki/phiki:^2.0

# 2. Pages (scaffold in app, then move into the module and fix namespaces to AcMarche\KnowledgeBase\Filament\Pages)
php artisan make:filament-page ListDocuments --no-interaction
php artisan make:filament-page ShowDocument --no-interaction
php artisan make:filament-page CreateDocument --no-interaction
php artisan make:filament-page EditDocument --no-interaction
php artisan make:filament-page EditNavigation --no-interaction

# 3. Support classes
php artisan make:class Documentation/Documentation --no-interaction      # then move into module
php artisan make:class Documentation/SecretScanner --no-interaction      # then move into module
php artisan make:class Documentation/ImageStore --no-interaction         # then move into module
php artisan make:controller ImageController --invokable --no-interaction # then move to src/Http/Controllers

# 4. Tests
php artisan make:test --pest KnowledgeBaseAccessTest --no-interaction    # move to modules/KnowledgeBase/tests/Feature
php artisan make:test --pest DocumentationTest --no-interaction
php artisan make:test --pest DocumentPagesTest --no-interaction
php artisan make:test --pest ImageTest --no-interaction

# 5. Roles in DB
php artisan security:sync-roles   # use the actual signature of SyncRolesCommand (check php artisan list)
```

No migrations.

---

## 2. Module skeleton

```
modules/KnowledgeBase/
├── BLUEPRINT.md
├── composer.json
├── config/
│   ├── knowledge-base.php
│   └── database.php
├── resources/views/filament/pages/
│   ├── show-document.blade.php
│   └── partials/sidebar.blade.php
├── routes/web.php
├── src/
│   ├── Documentation/
│   │   ├── Documentation.php
│   │   ├── ImageStore.php
│   │   └── SecretScanner.php
│   ├── Enums/RolesEnum.php
│   ├── Http/Controllers/ImageController.php
│   ├── Filament/Pages/
│   │   ├── ListDocuments.php
│   │   ├── ShowDocument.php
│   │   ├── CreateDocument.php
│   │   ├── EditDocument.php
│   │   └── EditNavigation.php
│   ├── KnowledgeBaseAccess.php
│   └── Providers/
│       ├── KnowledgeBaseServiceProvider.php
│       └── Filament/KnowledgeBasePanelProvider.php
└── tests/Feature/
    ├── KnowledgeBaseAccessTest.php
    ├── DocumentationTest.php
    ├── DocumentPagesTest.php
    └── ImageTest.php
```

### 2.1 composer.json
Copy `modules/Note/composer.json` and replace `Note` with `KnowledgeBase` /
`knowledge-base`. Description: "IT knowledge base (Markdown) for AcMarche".
Provider: `AcMarche\\KnowledgeBase\\Providers\\KnowledgeBaseServiceProvider`.
Remove the Factories/Seeders autoload entries (there are no models).

### 2.2 config/knowledge-base.php
```php
return [
    'filesystems.disks.knowledge-base' => [
        'driver' => 'local',
        'root' => storage_path('app/private/knowledge-base'),
        'visibility' => 'private',
        'serve' => false,
        'throw' => true,
    ],
    'vault_url' => env('KNOWLEDGE_BASE_VAULT_URL'),
];
```
`registerFilesystemDisks()` in `ModuleServiceProviderTrait` registers the dotted
disk key. That trait is already used by every module.

### 2.3 config/database.php
`registerDatabaseConnection()` `require`s this file, so it must exist:
```php
return ['connections' => []];
```

### 2.4 KnowledgeBaseServiceProvider
Same as `NoteServiceProvider`. `moduleName()` returns `'knowledge-base'`.
`public static int $module_id = 65;`. Before using 65, confirm it is free in
the `modules` table (64 = Note is the highest in code). If it is taken, use the
next free id.

### 2.5 KnowledgeBasePanelProvider
Copy `NotePanelProvider`. Changes:
- `->id('knowledge-base-panel')->path('knowledge-base')->brandName('Base de connaissances')`
- `->colors(['primary' => Color::Indigo])`
- `->discoverPages(in: $path.'Filament/Pages', for: 'AcMarche\\KnowledgeBase\\Filament\\Pages')`
- Remove `discoverResources` and `discoverWidgets`. Remove the `resource*Redirect` calls.

Register it in `bootstrap/providers.php` (alphabetical, after `Issep`).

### 2.6 SyncRolesCommand
In `modules/Security/src/Console/Commands/SyncRolesCommand.php`, add the imports
`AcMarche\KnowledgeBase\Enums\RolesEnum as RoleEnumKnowledgeBase` and
`AcMarche\KnowledgeBase\Providers\KnowledgeBaseServiceProvider`, plus the entry
`[KnowledgeBaseServiceProvider::class, RoleEnumKnowledgeBase::cases()]`.

---

## 3. Domain classes (no models)

### 3.1 RolesEnum — `src/Enums/RolesEnum.php`
Shape of `AcMarche\Mileage\Enums\RolesEnum` (implements `HasLabel`, plus
`getRoles(): array<string,string>`):

| Case                         | Value                        | Label                          |
| ---------------------------- | ---------------------------- | ------------------------------ |
| `ROLE_KNOWLEDGE_BASE_ADMIN`  | `ROLE_KNOWLEDGE_BASE_ADMIN`  | `Base de connaissances (édition)` |
| `ROLE_KNOWLEDGE_BASE`        | `ROLE_KNOWLEDGE_BASE`        | `Base de connaissances (lecture)` |

### 3.2 KnowledgeBaseAccess — `src/KnowledgeBaseAccess.php`
`final class` with static methods. This is the **single authorization rule**,
and every page calls it:

```php
public static function canRead(?User $user): bool
//  $user !== null && (self::isIntranetAdmin($user) || $user->hasOneOfThisRoles(RolesEnum::getRoles()))

public static function canWrite(?User $user): bool
//  $user !== null && (self::isIntranetAdmin($user) || $user->hasRole(RolesEnum::ROLE_KNOWLEDGE_BASE_ADMIN->value))

private static function isIntranetAdmin(User $user): bool
//  $user->isAdministrator() || $user->hasRole(AcMarche\Security\Enums\RolesEnum::INTRANET_ADMIN->value)
```

Why this matters: `App\Models\User::canAccessPanel()` returns `true` for everyone, so
the panel itself does **not** restrict access. Each page must override
`canAccess()`. Filament checks `canAccess()` on the route and on every
Livewire update, and it also hides the nav item
(https://filamentphp.com/docs/5.x/navigation/custom-pages#authorization).

### 3.3 Documentation — `src/Documentation/Documentation.php`
`final class`, constructor `public function __construct(private Filesystem $disk)`.
Bind it in the service provider `register()` with `$this->app->scoped(...)`
(Octane rule: no singleton holding state), using `Storage::disk('knowledge-base')`.
All methods validate the slug with the slug rule and throw
`InvalidArgumentException` on failure.

| Method | Return | Behaviour |
| ------ | ------ | --------- |
| `isValidSlug(string $slug): bool` | bool | regex + `!== 'documentation'` |
| `exists(string $slug): bool` | bool | `$disk->exists("$slug.md")` |
| `get(string $slug): string` | raw markdown | throws `FileNotFoundException` if missing |
| `title(string $slug): string` | string | title rule |
| `hash(string $slug): string` | sha1 of content | used for edit-conflict detection |
| `lastModified(string $slug): CarbonImmutable` | | from `$disk->lastModified()` |
| `put(string $slug, string $markdown): void` | | normalise line endings to `\n` and ensure a trailing newline, then write |
| `delete(string $slug): void` | | delete file |
| `all(): Collection<int, array{slug:string,title:string,section:?string,updated_at:CarbonImmutable,content:string}>` | | every `*.md` except `documentation.md`, sorted by title |
| `navigation(): array<int, array{section:string, pages: array<int, array{slug:string,title:string}>}>` | | parse `documentation.md`: a line `- ## X` opens a section, a line `- [Title](slug)` adds a page. Drop slugs that fail the slug rule **or** do not exist |
| `navigationSource(): string` / `putNavigation(string $markdown): void` | | raw read/write of `documentation.md` (`''` if missing) |
| `render(string $slug): string` | HTML | `Str::markdown($md, ['html_input' => 'escape', 'allow_unsafe_links' => false], [new PhikiExtension(['light' => Theme::GithubLight, 'dark' => Theme::GithubDark])])`. Raw HTML is escaped, never allowed. Fenced code blocks with a language (` ```bash `, ` ```php `, ` ```nginx `…) are highlighted. A block with no language, or an unknown one, renders as plain `<pre><code>` |

Imports for `render()`: `Phiki\Adapters\CommonMark\PhikiExtension`, `Phiki\Theme\Theme`
(Docs: https://github.com/phikiphp/phiki/blob/2.x/docs/commonmark.mdx). The third
argument of `Illuminate\Support\Str::markdown()` is the extension list (verified in
the installed framework). Phiki's lexing is CPU-heavy, so cache the rendered HTML:
`Cache::remember('knowledge-base:html:'.$slug.':'.$this->hash($slug), now()->addDay(), ...)`.
The key includes the content hash, so an edit invalidates it naturally.

**Dark mode CSS.** Phiki puts the dark theme into CSS variables. Filament toggles
dark mode with a `.dark` class on `<html>`, not with `prefers-color-scheme`. Append
this to `resources/css/filament/admin/theme.css` (the theme every module panel uses),
then run `npm run build`:

```css
.dark .phiki,
.dark .phiki span {
    color: var(--phiki-dark-color) !important;
    background-color: var(--phiki-dark-background-color) !important;
    font-style: var(--phiki-dark-font-style) !important;
    font-weight: var(--phiki-dark-font-weight) !important;
    text-decoration: var(--phiki-dark-text-decoration) !important;
}
.fi-prose pre.phiki { overflow-x: auto; padding: 1rem; border-radius: 0.5rem; }
```

### 3.3b ImageStore — `src/Documentation/ImageStore.php`
`final class`, constructor `private Filesystem $disk` (same disk, bound with `scoped()`).

| Method | Behaviour |
| ------ | --------- |
| `isValidFilename(string $filename): bool` | image filename rule (§0) |
| `store(UploadedFile $file): string` (Livewire's `TemporaryUploadedFile` extends it) | extension from `$file->guessExtension()` (`jpeg` is normalised to `jpg`). If the extension is not in `png,jpg,gif,webp`, throw `ValidationException`. Name = `Str::uuid()->toString().'.'.$ext`. Write with `$file->storeAs('images', $name, 'knowledge-base')`. **Never** call `setVisibility('public')`. Returns the filename |
| `url(string $filename): string` | `route('knowledge-base.image', ['filename' => $filename], absolute: false)`, i.e. `/knowledge-base/images/{uuid}.png`. The URL is relative, so it still works if the domain changes |
| `exists(string $filename): bool` | `isValidFilename` && `$disk->exists("images/$filename")` |

### 3.3c Image route — `routes/web.php` + `ImageController`
`ModuleServiceProviderTrait::bootModule()` already loads `routes/web.php`.

```php
Route::middleware('web')->prefix('knowledge-base')->name('knowledge-base.')->group(function (): void {
    Route::get('images/{filename}', ImageController::class)
        ->where('filename', '[0-9a-f\-]{36}\.(png|jpg|gif|webp)')
        ->name('image');
});
```

`ImageController::__invoke(string $filename, ImageStore $images)`, in this order:
1. `abort_unless(KnowledgeBaseAccess::canRead(auth()->user()), 403);`. Guests also get 403.
2. `abort_unless($images->exists($filename), 404);`
3. `return Storage::disk('knowledge-base')->response("images/$filename", headers: ['Cache-Control' => 'private, max-age=3600', 'X-Content-Type-Options' => 'nosniff']);`

`/knowledge-base/images/...` does not collide with the panel's page routes (`/knowledge-base/docs...`).

Why this is secure: `<img src="/knowledge-base/images/…">` is requested by the
browser with the user's session cookie. Each request is authorised again, so an
image link copied outside the intranet or opened by a user without the role is
refused. `private` caching keeps proxies from storing it. SVG is excluded
(it can carry scripts).

### 3.4 SecretScanner — `src/Documentation/SecretScanner.php`
`final class`, method `findings(string $markdown): array<int, array{line:int, rule:string}>`.
Rules (case-insensitive, checked per line):

| rule key       | regex |
| -------------- | ----- |
| `private-key`  | `/-----BEGIN [A-Z ]*PRIVATE KEY-----/` |
| `password`     | `/\b(pass(word)?\|pwd\|mot de passe\|mdp)\s*[:=]\s*\S{4,}/i` |
| `token`        | `/\b(api[_-]?key\|secret\|token)\s*[:=]\s*[A-Za-z0-9_\-\/+]{12,}/i` |
| `aws-key`      | `/\bAKIA[0-9A-Z]{16}\b/` |

Inside a regex, `\|` in the table means a literal `|` (alternation).

---

## 4. Filament pages

All pages live in the `AcMarche\KnowledgeBase\Filament\Pages` namespace and extend
`Filament\Pages\Page`. Docs: https://filamentphp.com/docs/5.x/navigation/custom-pages

**Route parameters:** the installed `HasRoutes::routes()` registers
`Route::get('/'.static::getSlug(), static::class)`, so a `$slug` such as
`'docs/{document}'` produces a Laravel route parameter, which Livewire passes to
`mount(string $document)`. `Page::getUrl(['document' => $slug])` fills it in.
Pages with a parameter **must** set `protected static bool $shouldRegisterNavigation = false;`.

**Pages rule (from `.ai/rules/pages.md`):** do all route and param validation and every
`abort_*` in `mount()`. Store the slug in a public property. Never call
`request()` from `getTitle()` or other render-time methods.

### 4.1 ListDocuments (nav entry, home of the panel)
- Slug: `docs`. Nav label `Documentation`, icon `Heroicon::OutlinedBookOpen`, sort 1.
- `canAccess()`: `KnowledgeBaseAccess::canRead(auth()->user())`.
- Implements `Filament\Tables\Contracts\HasTable` and uses `Filament\Tables\Concerns\InteractsWithTable`.
  Table with **custom data**: `->records(fn (?string $search, ?string $sortColumn, ?string $sortDirection): Collection => ...)`
  (Docs: https://filamentphp.com/docs/5.x/tables/custom-data). Source is
  `app(Documentation::class)->all()`, keyed by slug. If `$search` is filled, keep rows
  whose `title` or `content` contains it (`Str::contains(..., ignoreCase: true)`). Sort in PHP.
- Columns:
  - Title. Component: `Filament\Tables\Columns\TextColumn::make('title')`. Docs: https://filamentphp.com/docs/5.x/tables/columns/text. Config: `->label('Titre')->sortable()->weight(FontWeight::SemiBold)`
  - Section. `Filament\Tables\Columns\TextColumn::make('section')`. Config: `->label('Section')->badge()->placeholder('Non classé')->sortable()`
  - Updated. `Filament\Tables\Columns\TextColumn::make('updated_at')`. Config: `->label('Modifié')->since()->sortable()`
- Table config: `->searchable()`, `->defaultSort('title')`, `->recordUrl(fn (array $record): string => ShowDocument::getUrl(['document' => $record['slug']]))`.
  Note: the project-wide `deferLoading()` applies, so tests must call `->loadTable()`.
- Header actions:
  - **create**. Component `Filament\Actions\Action::make('create')`. Docs https://filamentphp.com/docs/5.x/actions/overview. Location: header. Visibility & Authorization: `->visible(fn (): bool => KnowledgeBaseAccess::canWrite(auth()->user()))`. Behavior: `->url(CreateDocument::getUrl())`, label `Nouvelle page`, icon `Heroicon::Plus`.
  - **navigation**. `Filament\Actions\Action::make('navigation')`. Location: header. Same visibility. Behavior: `->url(EditNavigation::getUrl())`, label `Modifier le sommaire`, color gray.

### 4.2 ShowDocument
- Slug `docs/{document}`. No navigation. View `knowledge-base::filament.pages.show-document`.
- `public string $document;`
- `mount(string $document)`: `abort_unless($docs->isValidSlug($document) && $docs->exists($document), 404)`, then assign the property.
- `canAccess()`: `canRead`.
- `getTitle()`: `app(Documentation::class)->title($this->document)`.
- View layout, like laravel.com/docs: a two-column grid (`lg:grid-cols-[16rem_1fr]`).
  - Left column: `partials/sidebar.blade.php` loops over `navigation()` and shows section headings with links. The current slug is highlighted with `text-primary-600 font-semibold`.
  - Right column: `<article class="fi-prose max-w-none">{!! $html !!}</article>`, where `$html = render($this->document)`. Raw HTML is already escaped by CommonMark (§3.3), so the `{!! !!}` output is safe.
  - Under the article: "Modifié {{ diffForHumans }}".
  - If `config('knowledge-base.vault_url')` is set, show a callout: "Les mots de passe ne sont jamais stockés ici : voir le coffre-fort" with a link.
- Header actions:
  - **edit**. `Filament\Actions\Action::make('edit')`. Visibility: `canWrite`. Behavior: `->url(EditDocument::getUrl(['document' => $this->document]))`.
  - **delete**. `Filament\Actions\Action::make('delete')`. Visibility: `canWrite`. Authorization: re-check `canWrite` inside `->action()` and `abort_unless(..., 403)` **before** deleting. Behavior: `->requiresConfirmation()->color('danger')`, then `$docs->delete($this->document)`, success notification `Page supprimée`, redirect to `ListDocuments::getUrl()`. The `documentation.md` entry is left as is. `navigation()` drops entries whose file is missing (§3.3), so the dead link disappears from the sidebar.

### 4.3 CreateDocument
- Slug `create` (URL `/knowledge-base/create`, outside `docs/` so it can never be captured by `docs/{document}`). No navigation. `canAccess()`: `canWrite`.
- Uses `Filament\Schemas\Concerns\InteractsWithSchemas`, implements `Filament\Schemas\Contracts\HasSchemas`,
  and uses `Filament\Schemas\Concerns\RestrictsFileUploadsToSchemaComponents`
  (Docs: https://filamentphp.com/docs/5.x/advanced/security). `public ?array $data = []`. Form `statePath('data')`, `->columns(1)`.
- Fields:
  - **slug**. Component `Filament\Forms\Components\TextInput::make('slug')`. Docs https://filamentphp.com/docs/5.x/forms/text-input.
    Validation: `required`, `max:100`, `regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/`, `not_in:documentation,create`, and a closure rule that fails when `app(Documentation::class)->exists($value)` (message "Une page avec ce slug existe déjà").
    Config: `->label('Identifiant (URL)')->helperText('minuscules, chiffres et tirets — ex. vpn-forticlient')`.
  - **content**. Component `Filament\Forms\Components\MarkdownEditor::make('content')`. Docs https://filamentphp.com/docs/5.x/forms/markdown-editor.
    Validation: `required`, `string`, `max:500000`.
    Config:
    ```php
    MarkdownEditor::make('content')
        ->label('Contenu')
        ->default("# Titre\n\n")
        ->columnSpanFull()
        ->fileAttachmentsDisk('knowledge-base')
        ->fileAttachmentsAcceptedFileTypes(['image/png', 'image/jpeg', 'image/gif', 'image/webp'])
        ->fileAttachmentsMaxSize(5120)
        ->saveUploadedFileAttachmentUsing(fn (TemporaryUploadedFile $file): string => app(ImageStore::class)->store($file))
        ->getFileAttachmentUrlUsing(fn (string $file): string => app(ImageStore::class)->url($file))
    ```
    Imports: `Livewire\Features\SupportFileUploads\TemporaryUploadedFile`, `AcMarche\KnowledgeBase\Documentation\ImageStore`.
    **Lifecycle (verified in the installed `HasFileAttachments::saveUploadedFileAttachment()`):** when
    `saveUploadedFileAttachmentUsing` is set, its return value is used directly, and the
    built-in `store()` plus `setVisibility($path, 'public')` branch is **skipped**. The URL
    from `getFileAttachmentUrlUsing` is what the editor inserts into the Markdown
    (`![](/knowledge-base/images/{uuid}.png)`). Do **not** call `fileAttachmentsVisibility()`:
    on MarkdownEditor it throws a `LogicException`.
    Docs: https://filamentphp.com/docs/5.x/forms/markdown-editor#uploading-images-to-the-editor
    The upload runs through a Livewire request on a page whose `canAccess()` is `canWrite`, so
    readers cannot upload. `RestrictsFileUploadsToSchemaComponents` limits the upload
    target to this field.
- Footer/form action **save**: `$this->form->getState()`, re-check `canWrite` (403 if not), `put($slug, $content)`, run §4.6 secret warning, success notification `Page créée`, redirect to `ShowDocument::getUrl(['document' => $slug])`.

### 4.4 EditDocument
- Slug `docs/{document}/edit`. No navigation. `canAccess()`: `canWrite`.
- `mount(string $document)`: same 404 guard as ShowDocument. Store `$this->document`, store `$this->originalHash = hash($document)`, and fill the form with `['content' => get($document)]`.
- Field **content**: same spec as in CreateDocument (no slug field, because slugs cannot be renamed).
- **save** action, in this order:
  1. `canWrite`, otherwise 403.
  2. If `hash($this->document) !== $this->originalHash`: show a danger notification "La page a été modifiée par quelqu'un d'autre depuis l'ouverture. Rechargez avant d'enregistrer." and **do not write**.
  3. `put()`.
  4. Secret warning (§4.6).
  5. Redirect to ShowDocument.
- Header action **cancel**: url to ShowDocument, color gray.

### 4.5 EditNavigation
- Slug `navigation` (URL `/knowledge-base/navigation`), no navigation, `canAccess()`: `canWrite`. `navigation` stays in the reserved slugs.
- Field **source**. `Filament\Forms\Components\Textarea::make('source')`. Docs https://filamentphp.com/docs/5.x/forms/textarea. Validation `nullable|string|max:100000`. Config: `->label('documentation.md')->rows(25)->extraInputAttributes(['class' => 'font-mono'])->helperText('Format : "- ## Section" puis "    - [Titre](slug)"')`.
- Placeholder below: `Filament\Infolists\Components\TextEntry::make('available')` (or `Filament\Schemas\Components\Text`) lists the existing slugs that are **not** referenced yet, so editors can add them.
- **save** action: `canWrite` check, then `putNavigation()`, then notification, then redirect to ListDocuments.

### 4.6 Secret warning (shared, Create/Edit)
After a successful `put()`, run `$findings = app(SecretScanner::class)->findings($content)`.
If it is not empty, send a persistent warning:
`Filament\Notifications\Notification::make()->warning()->persistent()->title('Possible secret détecté')->body('Lignes : 12, 40 — déplacez ces informations dans le coffre-fort.')->send()`.
The save is **not** blocked (the user chose warn-only).

**Consistency check:** the slug rule from §0 is the only rule. It is applied in
create validation (`regex` + `not_in` + exists), in the `mount()` guards (`isValidSlug`), in
`navigation()` filtering, and in every `Documentation` method. Reserved list: `documentation`,
`create`, `navigation`. A slug that passes create validation must always reopen
through ShowDocument/EditDocument (roundtrip test §6).

---

## 5. Authorization summary

| Who                                           | List / Show / images | Create / Edit / Delete / Edit navigation / upload |
| --------------------------------------------- | -------------------- | ------------------------------------------------- |
| `ROLE_KNOWLEDGE_BASE_ADMIN`                   | yes                  | yes                                               |
| `ROLE_KNOWLEDGE_BASE`                         | yes                  | no (actions hidden, pages 403)                    |
| intranet admin (`is_administrator` or `ROLE_INTRANET_ADMIN`) | yes   | yes                                               |
| any other authenticated user, or a guest      | no (403, no nav item)| no                                                |

Denial must happen before any write. Mutating actions re-check `canWrite`
inside `->action()` even though they are hidden.

---

## 6. Tests (Pest, `modules/KnowledgeBase/tests/Feature`)

Setup: `Storage::fake('knowledge-base')`. Users come from `User::factory()`. Roles are attached as in
Mileage tests: `$role = AcMarche\Security\Models\Role::factory()->create(['name' => RolesEnum::ROLE_KNOWLEDGE_BASE_ADMIN->value]); $user->roles()->attach($role);`.
Set the current panel with `Filament::setCurrentPanel('knowledge-base-panel')`.
Livewire helper: `Livewire\livewire(...)`. Tables need `->loadTable()` before row assertions.

**KnowledgeBaseAccessTest**
- A user without a role gets `assertForbidden()` on ListDocuments and on ShowDocument for an existing slug.
- A user with `is_administrator = true` and no module role, and a user with only `ROLE_INTRANET_ADMIN`, can both open every page, including the write pages.
- A reader can see the ListDocuments rows and the ShowDocument content. The `create`, `edit` and `delete` actions are `assertActionHidden`. CreateDocument, EditDocument and EditNavigation are `assertForbidden()`.
- An editor sees the actions and can open every page.

**DocumentationTest** (service, no Livewire)
- `isValidSlug`: accepts `vpn-forticlient` and rejects `../etc/passwd`, `Vpn`, `a--b`, `documentation`, `create`, `navigation`, `''`.
- `title()`: uses the first `# ` line. With no heading, it returns `Str::headline($slug)`.
- `navigation()`: parses sections and order from the sample in §0. It drops a listed slug whose file is missing and an entry with an invalid slug.
- `render()`: escapes raw `<script>` (the output contains `&lt;script&gt;`) and drops a `javascript:` link.
- `render()`: a ` ```php ` fenced block produces `<pre class="phiki` with inline `style=` colours and the `--phiki-dark-` variables. A fenced block with no language still renders its code text, escaped.
- `SecretScanner`: flags `password: Hunter2xyz`, `-----BEGIN RSA PRIVATE KEY-----` and `AKIAABCDEFGHIJKLMNOP`. It does **not** flag `Le mot de passe est dans le coffre-fort.`

**DocumentPagesTest**
- Create roundtrip: an editor creates `deploy-laravel-app`. The file exists with normalised content, the redirect goes to ShowDocument, and ShowDocument renders the H1.
- Create validation: an existing slug, `Bad Slug` and `documentation` each give `assertHasFormErrors(['data.slug'])`. Nothing is written.
- Edit: saving changes the file. If the file changes on disk after mount, saving shows the conflict and leaves the disk content untouched.
- Delete: the editor's delete removes the file and redirects. A reader calling the action is denied and the file still exists.
- ShowDocument with an unknown or invalid slug gives `assertNotFound()`.
- Secret warning: saving content with `password: Hunter2xyz` writes the file **and** sends the warning notification (`assertNotified('Possible secret détecté')`).

**ImageTest**
- `ImageStore::store()` with `UploadedFile::fake()->image('a.png')`: the file lands in `images/` on the fake disk under a uuid name that matches the filename rule. `url()` returns `/knowledge-base/images/{name}`.
- `ImageStore::store()` with a fake `.svg` or `.pdf` throws `ValidationException`, and nothing is written.
- GET `route('knowledge-base.image', …)`:
  - a reader gets `assertOk()` with `Cache-Control` containing `private`;
  - a user without a role gets 403;
  - an intranet admin without a module role gets 200;
  - a guest gets 403;
  - a reader asking for a valid but missing uuid gets 404.
- Path traversal: GET `/knowledge-base/images/..%2Fvpn.md` and `/knowledge-base/images/vpn.md` get 404 (the route constraint rejects them). The contents of the `.md` file are never returned.
- Upload through CreateDocument: no assertion on the editor JS. Cover the `saveUploadedFileAttachmentUsing` callback by calling `ImageStore` directly (above).

Run with `php artisan test --compact modules/KnowledgeBase/tests`, then `vendor/bin/pint --dirty --format agent`.

---

## 7. Out of scope (not agreed — ask before adding)

- Page history or versions (explicitly not wanted).
- Cleanup of orphaned images. Deleting a page or removing `![](…)` leaves the file in `images/`.
- Non-image attachments (PDF, configs…).
- Per-section access roles.
- Global search integration.
