---
paths:
  - 'modules/Mileage/**'
---

# Mileage

## Production URL of a declaration
The Laravel app is what serves https://intranet.marche.be — a declaration is at https://intranet.marche.be/mileage/declarations/{id}/view (Filament `mileage-panel`), not the legacy Symfony /declaration/{id}.

Do not derive it from APP_URL: locally that is 127.0.0.1:8000. Build links with DeclarationResource::getUrl('view', ['record' => $id], panel: 'mileage-panel'), as VerifyTripRatesCommand does.

## Contact details and IBAN of a declaration
A declaration copies the address/IBAN it was created with, so those columns go stale. Never display `street`, `postal_code`, `city` or `iban` directly: use the `display_*` accessors on `Declaration`, which read `personal_information` (joined on `user_add` = `username`) first and fall back to the declaration's own columns field by field.

The raw `iban` column stays visible in one place only: when `hasOutdatedIban()` is true, show the current IBAN and note the account the declaration was actually made with.

## `UserResource` authorization is not policy-driven
`AcMarche\Mileage\Policies\UserPolicy` is never resolved by the gate: its model is `App\Models\User`, so Laravel only looks for `App\Models\Policies\UserPolicy` / `App\Policies\UserPolicy`. Registering it with `Gate::policy()` is not an option either — the Security and Pst modules expose their own user resources over the same model.

`UserResource` therefore overrides `canViewAny()`, `canView()`, `canCreate()`, `canEdit()`, `canDelete()`, `canDeleteAny()`, `canRestore*()` and `canForceDelete*()` and calls `UserPolicy` itself, keeping `/mileage/users` reserved to `ROLE_FINANCE_DEPLACEMENT_ADMIN` and global administrators. Add any new ability override there; the policy stays the single source of truth.

The other Mileage policies *are* auto-discovered (`AcMarche\Mileage\Models\X` → `AcMarche\Mileage\Policies\XPolicy`), so their resources need no override.

## Budget article label
A budget article is always shown as `functional_code - economic_code name` (`BudgetArticle::$display_name`). Select options come from `BudgetArticle::displayNameOptions()` — pass `'id'` when the field stores the id instead of the name. On a declaration use `display_budget_article`, which resolves the article from the name stored in `budget_article` and falls back to that bare name when the article is gone.
