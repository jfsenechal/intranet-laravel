# Filament Security Audit — Remediation Plan

## 1. Summary

Audit of how this application **uses** Filament v5, across all source roots
(`app/` and the ~28 module packages under `modules/*/src`). The application
exposes a large number of Filament panels (one `PanelProvider` per module plus
the root `app/Providers/Filament/AdminPanelProvider.php`), backed by ~50 Eloquent
policies registered through `Gate::policy()` in each module service provider.
Authentication is LDAP-based via a custom `app/Filament/Pages/Auth/Login.php`;
there is **no multi-tenancy** (no `tenant()` panel config, no tenant FK columns),
so checks D3 and D5 are N/A.

The dominant finding is systemic (F-01): bulk-delete authorization fails open
across essentially every resource because no policy defines `deleteAny()` and
`strictAuthorization()` is not enabled on any panel. The remaining findings are
unrestricted public-disk uploads (with sensitive HR PII), unsanitised rich-text
and incoming-email HTML, raw URL attributes, an unescaped `allowHtml` label,
unscoped HR list queries, and an unconditional `canAccessPanel()`.

Per-category distinct-finding counts (totals equal §2 entries):

| Category | Findings |
|---|---|
| A. Access Control | 1 (F-01) |
| B. File Uploads & RCE | 1 (F-02) |
| C. XSS & Injection | 4 (F-03 … F-06) |
| D. Query Scoping & Data Exposure | 2 (F-07, F-08) |
| E. Dependencies | 0 |
| **Total** | **8** |

---

## 2. Findings

### A. Access Control

#### [F-01] Bulk delete fails open — no policy defines `deleteAny()`

Check: A1
Location: every policy under `modules/*/src/Policies/*Policy.php` whose `delete()`
does real work, paired with a `DeleteBulkAction` (see list below).
Component: `Filament\Actions\DeleteBulkAction`
Docs: https://filamentphp.com/docs/5.x/resources/deleting-records#authorization &
https://filamentphp.com/docs/5.x/actions/delete#improving-the-performance-of-delete-bulk-actions

Issue: No panel calls `->strictAuthorization()` (verified: zero matches in
`app/`, `modules/`, `config/`). With strict mode off, Filament's
`get_authorization_response()` (`vendor/filament/filament/src/helpers.php:31`)
handles a *missing* policy method by running only the policy's `before()`
callback and then returning `Response::allow()` when that callback does not
return `false`. Every `DeleteBulkAction` authorizes the whole batch once against
`deleteAny()`. Because **no policy in the codebase defines `deleteAny()`**, the
check returns allow, so any user who can reach a resource's list (passes
`viewAny()`) can bulk-delete its records regardless of the stricter per-record
`delete()` policy. The gap is exploitable wherever `viewAny()` is broader than
`delete()`. High-impact confirmed cases:

- `modules/Courrier/src/Policies/IncomingMailPolicy.php` — `viewAny()` returns
  `true` (any authenticated user) but `delete()` requires an `_ADMIN` courrier
  role. Any logged-in user can bulk-delete incoming mail.
- `modules/Hrm/src/Policies/EmployeePolicy.php` — `viewAny()` = `hasAnyHrmRole()`
  (includes read-only roles) but `delete()` = `isAdmin()`. Any HR reader can
  bulk-delete employees.
- `modules/Security/src/Policies/UserPolicy.php` (`App\Models\User`) — backs the
  `DeleteBulkAction` in both `app/Filament/Resources/Users/Tables/UsersTable.php:45`
  and `modules/Security/src/Filament/Resources/Users/Tables/UserTables.php:79`;
  `viewAny()`/`delete()` are both intranet-admin here, so the practical gap is
  small, but the missing guard is the same class of bug.

`DeleteBulkAction` is present on ~90 tables/relation managers (`grep -rn
"DeleteBulkAction::make()" app modules`). No `ForceDeleteBulkAction` or
`RestoreBulkAction` exists anywhere, so `forceDeleteAny()`/`restoreAny()` are not
needed — only `deleteAny()` is missing.

Fix: For each policy whose `delete()` is a record-independent role check (all of
them are — e.g. `return $this->isAdmin($user);`), add a `deleteAny()` returning
the **same expression** as `delete()`:

```php
public function deleteAny(User $user): bool
{
    return $this->isAdmin($user); // mirror this policy's delete() body exactly
}
```

Apply to (at minimum) every policy that backs a `DeleteBulkAction`:
`modules/Courrier/src/Policies/{IncomingMailPolicy,SenderPolicy,ServicePolicy,CategoryPolicy,RecipientPolicy}.php`,
`modules/Hrm/src/Policies/*Policy.php` (all),
`modules/Mileage/src/Policies/{DeclarationPolicy,RatePolicy,TripPolicy,BudgetArticlePolicy,PersonalInformationPolicy}.php`,
`modules/MealDelivery/src/Policies/*Policy.php` (all),
`modules/ActivityManager/src/Policies/*Policy.php`,
`modules/News/src/Policies/*Policy.php`,
`modules/Conseil/src/Policies/*Policy.php`,
`modules/AldermenAgenda/src/Policies/*Policy.php`,
`modules/Document/src/Policies/*Policy.php`,
`modules/Publication/src/Policies/*Policy.php`,
`modules/Pst/src/Policies/*Policy.php`,
`modules/College/src/Policies/*Policy.php`,
`modules/Offenses/src/Policies/*Policy.php`,
`modules/Agent/src/Policies/*Policy.php`,
`modules/CpasLibrary/src/Policies/*Policy.php`,
`modules/Ad/src/Policies/*Policy.php`,
`modules/GuichetHdv/src/Policies/*Policy.php`,
`modules/StreetWatch/src/Policies/*Policy.php`,
`modules/SportsActivities/src/Policies/*Policy.php`,
`modules/Mediation/src/Policies/*Policy.php`,
`modules/Telecommunication/src/Policies/*Policy.php`,
`modules/Security/src/Policies/{UserPolicy,RolePolicy,ModulePolicy,TabPolicy}.php`.
(`App\Models\User` also needs a `deleteAny()` for the root `UsersTable`.)

Project-wide alternative (defence-in-depth, recommended in addition): enable
`->strictAuthorization()` on each `PanelProvider` so a future missing `*Any()`
throws instead of failing open — see §5.

Verify: see Recommended Tests — assert a non-admin who passes `viewAny` cannot
execute the `DeleteBulkAction`.

---

### B. File Uploads & RCE

#### [F-02] Unrestricted file types on the web-served `public` disk (and sensitive PII stored publicly)

Check: B2
Location (all resolve to disk `public`, no `acceptedFileTypes`/`image()`/`avatar()`):
- `modules/News/src/Filament/Resources/News/Schemas/NewsForm.php:37` (`medias`)
- `modules/Conseil/src/Filament/Resources/Agendas/Schemas/AgendaForm.php:37` (`file_name`)
- `modules/AldermenAgenda/src/Filament/Resources/Event/Schemas/EventForm.php:47,53` (`file1_name`, `file2_name`)
- `modules/Document/src/Filament/Resources/Documents/Schemas/DocumentForm.php:38` (`file_path`)
- `modules/Ad/src/Filament/Resources/ClassifiedAd/Schemas/ClassifiedAdForm.php:38` (`medias`)
- `modules/MailingList/src/Filament/Resources/Emails/Schemas/EmailForm.php:45` (`attachments`)
- `modules/Pst/src/Filament/Resources/ActionPst/Schemas/MediaForm.php:26` (`file_name`)
- `modules/Pst/src/Filament/Resources/FollowUp/Schemas/FollowUpForm.php:20` (`icon`)
- `modules/Offenses/src/Filament/Resources/Offenses/Schemas/OffenseForm.php:36` (`file_name`)
- `modules/Hrm/src/Filament/Resources/Contracts/Schemas/ContractForm.php:118,122`
- `modules/Hrm/src/Filament/Resources/HrDocuments/Schemas/HrDocumentForm.php:20`
- `modules/Hrm/src/Filament/Resources/Evaluations/Schemas/EvaluationForm.php:58,63`
- `modules/Hrm/src/Filament/Resources/Diplomas/Schemas/DiplomaForm.php:26`
- `modules/Hrm/src/Filament/Resources/Applications/Schemas/ApplicationForm.php:65`
- `modules/Hrm/src/Filament/Resources/Trainings/Schemas/TrainingForm.php:103`
- `modules/Hrm/src/Filament/Resources/Valorizations/Schemas/ValorizationForm.php:45`
- `modules/Hrm/src/Filament/Resources/Employees/Schemas/EmployeeForm.php:205` (`candidate_file_name`)
- `modules/Telecommunication/src/Filament/Resources/Telephones/RelationManagers/AttachmentsRelationManager.php:31`

Component: `Filament\Forms\Components\FileUpload`
Docs: https://filamentphp.com/docs/5.x/forms/file-upload#file-type-validation

Issue: These fields target the `public` disk, which is web-served via
`storage:link`. With no `acceptedFileTypes()` allowlist, `FileUpload` accepts any
file, so a renamed `.php` file lands under `public/storage/...` with an
executable extension → RCE if PHP-FPM executes it. Additionally, several of these
fields store **sensitive PII** (HR contracts, diplomas, job applications,
evaluations, valorizations, candidate files; offense records; council documents)
with `->visibility('public')`, so the stored files are readable by anyone with the
URL **without authentication**. (Fields already restricted via `->image()`/
`->avatar()` — avatar, sender logo, Odd icon, employee photo — and the Courrier
`attachment_file` which uses `->acceptedFileTypes(config('courrier.allowed_mime_types'))`
— are not in scope. Private-disk fields on `local`/`cpas-library` are hygiene-only;
see §5.)

Fix: Add an explicit type allowlist to every field listed above, e.g.

```php
->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
```

and for the PII-bearing HR/Document/Offense fields, additionally move storage off
the public disk: replace `->disk('public')->visibility('public')` with
`->disk('local')->visibility('private')` and serve downloads through a
policy-gated action/route.

Verify: feature test uploading a `.php` payload to a listed field asserts a
validation error; assert sensitive uploads are not retrievable on the `public`
disk URL without auth.

---

### C. XSS & Injection

#### [F-03] Rich-editor HTML rendered raw in mail/notification views

Check: C1
Location:
- `modules/Conseil/resources/views/mail/notification.blade.php:20` (`{!! $body !!}` — `RichEditor` `body`, `modules/Conseil/src/Filament/Pages/NotifyRecipients.php:91`)
- `modules/Ad/resources/views/mail/ad-email.blade.php:38` (`{!! $classifiedAd->content !!}` — `RichEditor` `content`)
- `modules/MailingList/views/emails/newsletter.blade.php:55,59` (`{!! $body !!}`, `{!! $footer !!}` — `RichEditor` `body`/`footer`)
- `modules/College/resources/views/mail/notification.blade.php:23` (`{!! $body !!}` — `RichEditor` `message`)
- `modules/Hrm/resources/views/mail/telework/employee_hr_result.blade.php:20` (`hr_notes`)
- `modules/Hrm/resources/views/mail/telework/employee_manager_result.blade.php:26` (`manager_validation_notes`)
- `modules/Hrm/resources/views/mail/telework/manager_validation.blade.php:16,19` (`variable_day_reason`, `employee_notes`)

Component: Blade `{!! !!}` echoing `RichEditor`-backed attributes
Docs: https://filamentphp.com/docs/5.x/forms/rich-editor#security

Issue: These attributes hold raw user-authored HTML from a `RichEditor` (stored
as HTML, not `->json()`), echoed with `{!! !!}` and **no** `sanitizeHtml()`.
Filament's own renderers auto-sanitise, but these hand-written mail views bypass
that. A lower-privileged author (e.g. an employee filling telework notes) can
inject markup that renders in a higher-privileged recipient's mail. (Realistic
impact is bounded by email-client HTML sanitisation, but the sink is genuinely
unsanitised.) Already-safe siblings: `MealDelivery .../route-sheet*.blade.php`
(`nl2br(e(...))`) and `Hrm .../pdf/employee.blade.php` (`strip_tags`).

Fix: wrap each echo in the sanitizer, e.g. in `notification.blade.php`:

```blade
{!! str($body)->sanitizeHtml() !!}
```

Verify: render each mailable with a `<script>`-laden field value and assert it is
stripped.

---

#### [F-04] Raw incoming-email HTML rendered in the Courrier inbox infolist

Check: C2
Location: `modules/Courrier/src/Filament/Resources/Inbox/Schemas/InboxInfolist.php:57-58`
Component: `Filament\Infolists\Components\TextEntry` with `->state(new HtmlString($content))->html()`
Docs: https://filamentphp.com/docs/5.x/infolists/text-entry#rendering-raw-html-without-sanitization

Issue: `$content = $record['html'] ?? $record['text'] ?? ''` is the raw HTML body
of an IMAP message (`modules/Courrier/src/Repository/ImapRepository.php:255`),
i.e. fully attacker-controlled by **external email senders**. Wrapping it in
`HtmlString` and passing it to `->html()` bypasses Filament's sanitizer (a plain
`->html()` on a string state would sanitise; a pre-built `Htmlable` does not), so
a crafted inbound email executes script in the staff member's panel session →
stored XSS.

Fix: sanitise the email body before display:

```php
->state(str($content)->sanitizeHtml()) // drop new HtmlString(...) and keep ->html()
```

For fully-untrusted email, prefer a strict Symfony `HtmlSanitizer` config (drop
inline `style`/`on*`). Verify with a test feeding a `<script>`/`<img onerror>`
email body and asserting it is neutralised.

---

#### [F-05] Raw model URL attribute emitted into `url()`

Check: C3
Location:
- `modules/Publication/src/Filament/Resources/Publications/Pages/ViewPublication.php:33` — `->url(fn (Publication $publication) => $publication->url, true)`
- `modules/App/src/Filament/Resources/Rsses/Tables/RssTables.php:26` — `->url(fn ($record): string => (string) $record->url, shouldOpenInNewTab: true)`

Component: action / column `->url()` closure returning a raw attribute
Docs: https://filamentphp.com/docs/5.x/advanced/security#validating-user-input

Issue: The URL is a raw DB attribute rendered into an `<a href>`. A stored
`javascript:`/`data:` value executes on click. Form-side `->url()` validation
does not protect the stored value (it can be set via seeder/import/direct write).

Fix: sanitise on output:

```php
->url(fn (Publication $publication) => \Illuminate\Support\Str::sanitizeUrl($publication->url), true)
```

```php
->url(fn ($record): ?string => \Illuminate\Support\Str::sanitizeUrl((string) $record->url), shouldOpenInNewTab: true)
```

Verify: a record with `url = 'javascript:alert(1)'` renders no clickable
`javascript:` href.

---

#### [F-06] Unescaped DB value in an `allowHtml()` option label

Check: C4
Location: `modules/Agent/src/Filament/Resources/Profiles/Schemas/ProfileForm.php:127` (the `$module->name` interpolation), `->allowHtml()` at line 131
Component: `Filament\Forms\Components\CheckboxList` with `->allowHtml()`
Docs: https://filamentphp.com/docs/5.x/forms/select#allowing-html-in-the-option-labels

Issue: Option labels are built as
`$module->name.' <span ...>('.e($module->description).')</span>'`. The
description is escaped but **`$module->name` is not**, and `->allowHtml()` renders
the label as raw HTML. A module name containing markup is injected unescaped.

Fix: escape the name too:

```php
e($module->name).' <span class="text-sm text-gray-500 dark:text-gray-400">('.e($module->description).')</span>'
```

Verify: a `Module` with `name = '<img src=x onerror=alert(1)>'` renders escaped in
the checkbox list.

---

### D. Query Scoping & Data Exposure

#### [F-07] Hrm Employee/Contract lists ignore the record-level ownership rule enforced in the policy

Check: D1
Location:
- `modules/Hrm/src/Filament/Resources/Employees/EmployeeResource.php` (no `getEloquentQuery()`; table at `.../Employees/Tables/EmployeeTables.php:31` only eager-loads)
- `modules/Hrm/src/Filament/Resources/Contracts/ContractResource.php` (no `getEloquentQuery()`; `.../Contracts/Tables/ContractTables.php` only filters)
Component: Resource list query vs. `EmployeePolicy::view()` / `ContractPolicy::view()`
Docs: https://filamentphp.com/docs/5.x/advanced/security#scoping-queries

Issue: `EmployeePolicy::view()`/`ContractPolicy::view()` delegate to
`canViewEmployee()`/`canViewContract()` in
`modules/Hrm/src/Policies/Concerns/HrmAuthorization.php`, which restrict per record
by organisation (CPAS/Ville top-employer slug) and by direction (a direction head
sees only their direction). But `viewAny()` is the broad `hasAnyHrmRole()`, and
the list query applies **no** equivalent scope (the `Employee` model's `booted()`
adds no ownership global scope; `bootHasUser()` only stamps the owner on write).
So a CPAS-only reader, or a direction head, sees **every** employee/contract row
(names, employer, dates — PII) in the table, contradicting the per-record `view()`
boundary. (By contrast, Courrier `IncomingMail` and Pst `Action` apply a
`DepartmentScope` global scope, and MailingList models apply `OwnerScope` — those
lists are scoped and are not findings.)

Fix: scope the list query to mirror the policy. In `EmployeeResource`:

```php
public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();
    $user = auth()->user();

    if ($user->isAdministrator() || $user->hasRole(RolesEnum::ROLE_GRH_ADMIN->value)) {
        return $query;
    }

    // Apply the same org/direction constraints as HrmAuthorization::canViewEmployee()
    return $query->where(function (Builder $q) use ($user): void {
        $q->where('username', $user->username);

        if ($user->hasRole(RolesEnum::ROLE_GRH_CPAS_READ->value)) {
            $q->orWhereHas('contracts.employer', fn ($e) => $e->whereTopSlug('cpas'));
        }
        if ($user->hasRole(RolesEnum::ROLE_GRH_VILLE_READ->value)) {
            $q->orWhereHas('contracts.employer', fn ($e) => $e->whereTopSlug('ville'));
        }
        if ($user->hasRole(RolesEnum::ROLE_GRH_DIRECTION->value)) {
            $q->orWhereHas('contracts', fn ($c) => $c->active()
                ->whereIn('direction_id', Direction::where('director', $user->username)->pluck('id')));
        }
    });
}
```

Mirror the same in `ContractResource::getEloquentQuery()` using
`canViewContract()`'s logic. Refactor the shared predicate into a query scope on
the model (or a `ScopedBy` global scope) so the table, widgets, and any other
query stay consistent. Verify a CPAS reader sees only CPAS employees in the table.

---

#### [F-08] `canAccessPanel()` returns `true` for every authenticated user

Check: D4
Location: `app/Models/User.php:185-188`
Component: `Filament\Models\Contracts\FilamentUser::canAccessPanel()`
Docs: https://filamentphp.com/docs/5.x/users#authorizing-access-to-the-panel

Issue: `canAccessPanel()` unconditionally returns `true`, so any LDAP-authenticated
user can enter **all** ~28 module panels. Panel entry is the front door; once
inside, the only remaining control is per-resource policies — and several are
permissive (e.g. Courrier `IncomingMailPolicy::viewAny()` returns `true`). The
absence of a panel-level gate means a user with no business role for a module
still reaches its navigation and any open resource. This may be intentional for an
intranet where every employee is trusted; **assumption: panel access should be
gated per module role.** If the open posture is deliberate, document it and rely
on tightening the permissive `viewAny()` policies instead.

Fix: gate on the panel and a corresponding role, e.g.

```php
public function canAccessPanel(Panel $panel): bool
{
    if ($this->isAdministrator()) {
        return true;
    }

    return $this->hasOneOfThisRoles(RolesEnum::rolesForPanel($panel->getId()));
}
```

Verify: a user without the module's role receives 403 on that panel's path while
retaining access to panels they are entitled to.

---

## 3. Checks Performed

| Check | Result | Note |
|---|---|---|
| A1 — bulk delete/restore missing `*Any()` | **Finding (F-01)** | No `deleteAny()` anywhere; no `strictAuthorization`; fails open. |
| A2 — Import bypasses create/update policy | Pass | Only `ContactImporter`; `Contact` has no policy and importer scopes records to `auth()` username. |
| A3 — Overridden `can*()` no longer invoked | Pass | Only `SignatureResource::canCreate()` (uniqueness gate); `canCreate` is still enforced for create pages/buttons. |
| A4 — Inline editable columns bypass `update()` | N/A | No `Toggle/Select/TextInput/Checkbox`-Column editable columns. |
| A5 — Livewire upload RPC without upload field | Pass | No custom `InteractsWithSchemas/Forms` components outside Resource/Page/RelationManager. |
| A6 — Work before authorization in lifecycle hooks | Pass | No `mount()`/`boot()`/`hydrate()` in custom pages performs DB writes, sends, or dispatches. |
| B1 — Path tampering on shared disks | Pass (see §5) | Public disks are already web-addressable; no confirmed sensitive+gradient case on the shared `local` disk. Global hardening noted in §5. |
| B2 — Upload accepts any file type | **Finding (F-02)** | Many `public`-disk fields lack a type allowlist; several hold PII with public visibility. |
| B3 — User-controlled file names → RCE | Pass | `preserveFilenames` is commented out; College's `getUploadedFileNameForStorageUsing` targets the private `local` disk (no execution path). |
| C1 — Unsanitised rich-editor output in Blade | **Finding (F-03)** | Raw `{!! !!}` of editor attributes in mail views. |
| C2 — Raw HTML bypasses sanitizer | **Finding (F-04)** | `HtmlString` of raw IMAP email body in the inbox infolist. |
| C3 — Unsafe URL schemes in `url()` | **Finding (F-05)** | Raw `url` attribute in Publication + Rss. |
| C4 — Unescaped option labels (`allowHtml`) | **Finding (F-06)** | Unescaped `$module->name` in Agent profile checkbox list. |
| C5 — Unescaped `extraAttributes()` | Pass | All dynamic `extra*Attributes` values are static styling / Alpine directives, not user data. |
| C6 — Unescaped validation messages | N/A | No `allowHtmlValidationMessages` usage. |
| C7 — User input in client-side JS | N/A | No `hiddenJs/visibleJs/actionJs/JsContent` usage. |
| D1 — List/widget ignores ownership rule | **Finding (F-07)** | Hrm Employee/Contract lists unscoped vs. record-dependent `view()`. |
| D2 — Sensitive attributes exposed to JS | Pass | Auth secrets (`password`, `remember_token`, `app_authentication_secret`, recovery codes) are in `#[Hidden]`; IBAN/wifi-password fields are user-owned data edited intentionally. |
| D3 — Models not auto-scoped to tenant | N/A | No multi-tenancy. |
| D4 — Over-permissive tenant-access methods | **Finding (F-08)** | `canAccessPanel()` returns `true`. (`getTenants`/`canAccessTenant` absent — no tenancy.) |
| D5 — `unique`/`exists` ignores tenant scope | N/A | No multi-tenancy. |
| E1 — Known vulnerabilities in deps | Pass | `composer audit` reports no advisories. |

---

## 4. Recommended Tests

Use Filament's testing helpers (https://filamentphp.com/docs/5.x/testing/overview).

- **F-01**: For a high-impact resource (Courrier `IncomingMail`, Hrm `Employee`),
  `actingAs` a user who passes `viewAny()` but fails `delete()`, then
  `livewire(List…::class)->callAction(TestAction::make('delete')->table())` (the
  bulk delete) and assert the records still exist / the action is unauthorised.
  Repeat as a regression test after adding `deleteAny()`.
- **F-02**: `livewire(CreateEmployee::class)` (or relevant page) → `fillForm` with a
  fake `.php` upload on `candidate_file_name` / `file1_name` → `assertHasFormErrors`.
- **F-03/F-04**: Build the mailable / inbox infolist state with a
  `'<script>alert(1)</script>'` field value and assert the rendered output does not
  contain `<script>`.
- **F-05**: Render the action/column with `url = 'javascript:alert(1)'` and assert
  the emitted href is null / not `javascript:`.
- **F-06**: Render `ProfileForm` with a `Module` whose `name` contains markup and
  assert it is HTML-escaped.
- **F-07**: `actingAs` a `ROLE_GRH_CPAS_READ` user;
  `livewire(ListEmployees::class)->assertCanSeeTableRecords($cpasEmployees)
  ->assertCanNotSeeTableRecords($villeEmployees)`.
- **F-08**: `actingAs` a user without a module's role and assert `403` on that
  panel path; assert an entitled user gets `200`.

---

## 5. Optional Hardening Tips

- **Enable `->strictAuthorization()` on every `PanelProvider`.** Verified
  condition: zero panels enable it and no policy defines any `*Any()` method, so
  bulk-action authorization fails open (root cause of F-01). With strict mode on,
  a missing `*Any()` throws a `LogicException` instead of silently allowing —
  turning future regressions into loud failures.
- **Register global `FileUpload::configureUsing(fn ($c) => $c->preventFilePathTampering())`
  (and `RichEditor::configureUsing(... ->preventFileAttachmentPathTampering())`).**
  Verified condition: many non-Spatie `FileUpload`/`RichEditor` writers share the
  private `local` disk with no per-field tampering protection and no global default
  (`grep -rn "preventFilePathTampering" app modules` → none). This defends against
  a future field on a shared private disk becoming an exfiltration primitive.
