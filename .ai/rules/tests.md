---
paths:
  - 'modules/**/tests/**'
---

# Tests

## Filament table and RichEditor assertions in tests
`app/Providers/AppServiceProvider::configureTable()` applies `deferLoading()` to every Filament table, so a freshly mounted list page renders no rows. Always chain `->loadTable()` before `assertCanSeeTableRecords()` / `assertCanNotSeeTableRecords()`, otherwise the assertion fails against a loading skeleton.

`assertHasFormErrors(['field' => 'required'])` does not match a required `RichEditor`: the error message is produced, but the failed-rule name Livewire compares against is not `required`. Assert the bare key (`assertHasFormErrors(['body'])`) for rich text fields; the rule name still works for `TextInput`.
