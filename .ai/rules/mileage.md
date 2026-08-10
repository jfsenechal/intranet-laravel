---
paths:
  - 'modules/Mileage/**'
---

# Mileage

## Production URL of a declaration
The Laravel app is what serves https://intranet.marche.be — a declaration is at https://intranet.marche.be/mileage/declarations/{id}/view (Filament `mileage-panel`), not the legacy Symfony /declaration/{id}.

Do not derive it from APP_URL: locally that is 127.0.0.1:8000. Build links with DeclarationResource::getUrl('view', ['record' => $id], panel: 'mileage-panel'), as VerifyTripRatesCommand does.
