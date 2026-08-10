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
