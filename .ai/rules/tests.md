---
paths:
  - 'modules/**/tests/**'
---

# Tests

## Filament table and RichEditor assertions in tests
`app/Providers/AppServiceProvider::configureTable()` applies `deferLoading()` to every Filament table, so a freshly mounted list page renders no rows. Always chain `->loadTable()` before `assertCanSeeTableRecords()` / `assertCanNotSeeTableRecords()`, otherwise the assertion fails against a loading skeleton.

`assertHasFormErrors(['field' => 'required'])` does not match a required `RichEditor`: the error message is produced, but the failed-rule name Livewire compares against is not `required`. Assert the bare key (`assertHasFormErrors(['body'])`) for rich text fields; the rule name still works for `TextInput`.

## A record-not-found on mount is a 404 response, not a thrown exception
`expect(fn () => livewire(ViewEmployee::class, ['record' => $id]))->toThrow(ModelNotFoundException::class)` never passes. Filament's `InteractsWithRecord::resolveRecord()` does throw when `resolveRecordRouteBinding()` finds nothing, but Livewire's test harness renders that exception into a response instead of letting it bubble out of `livewire()`, so the closure returns a `Testable` and the expectation fails with "Exception not thrown".

Assert the observable status instead: `livewire(ViewEmployee::class, ['record' => $id])->assertNotFound()`. Pair it with an `assertOk()` case on a record that *is* reachable — that is what proves the resource's `getEloquentQuery()` scope is doing the filtering (see the archived / non-agent / no-active-contract cases in `WhoIsWho`'s `ViewEmployeeTest`).

Same applies to the `abort_unless(..., 403)` in `authorizeAccess()`: assert `assertForbidden()`, not a thrown exception.
