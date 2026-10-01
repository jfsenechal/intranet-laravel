---
paths:
  - 'modules/Hrm/src/Policies/**'
---

# Policies

## Direction heads read every contract of an employee they can view
`HrmAuthorization::canViewContract()` grants a `ROLE_GRH_DIRECTION` user a contract in two cases: the contract's `direction_id` is one of the directions they head, or `canViewEmployee()` already grants them the contract's employee (i.e. that employee has an *active* contract in their direction). So a director opening one of their agents also reads that agent's contracts belonging to other directions, closed or not — intentional, it mirrors `EmployeePolicy::view()`.

## Filament gates every resource page on `viewAny`
`Filament\Resources\Pages\Concerns\CanAuthorizeResourceAccess` aborts 403 on `Resource::canAccess()` → `canViewAny()` *before* a page checks the record, so a record-level `view()` grant alone never opens a view page. `ContractPolicy::viewAny()` therefore grants `hasAnyHrmRole()` and `ContractPolicy::scopeVisibleTo()` (applied in `ContractResource::getEloquentQuery()`) keeps the listing, counts and global search to the contracts the user may open — the same pairing as `TeleworkPolicy` and `EmployeePolicy`. Because the scoped query never resolves a foreign record, the view page of a contract outside the user's reach answers 404, not 403.

`AbsencePolicy` and `TrainingPolicy` carry the same pairing, delegating to `HrmAuthorization::scopeRecordsOfVisibleEmployees()` — the shared scope for any employee-owned model whose `view()` grant is `canViewEmployee()` on the record's own employee. Use that helper rather than repeating the `whereHas('employee', ...)`.

`ValorizationPolicy::viewAny()` deliberately stays admin-only: Valorization has no Filament resource at all (`Resources/Valorizations/` holds only Schemas and Tables), so no page is gated on it. Its relation manager shows the tab via `VisibleWhenEmployeeIsViewable` and opens rows in a modal whose `ViewAction` authorizes only `view`, which already grants. Widening `viewAny` there would grant an ability nothing checks, and a later standalone resource would list every valorization unscoped — add `scopeVisibleTo()` and `getEloquentQuery()` at the same time if that resource is ever created.

## ReplicateContractAction is gated by two different mechanisms
`ReplicateContractAction` is reachable from two places and each resolves authorization differently:

- `ViewContract` header action → `ContractPolicy::replicate()` (via `Resources\Pages\Page::getDefaultActionAuthorizationResponse()`). Because `Filament\get_authorization_response()` treats a *missing* policy method as `allow()`, omitting `replicate()` makes the button visible to every HRM role. `AdminOnlyImplicitAbilities` declares it (admin-only) for all Hrm policies — keep the trait on any policy a non-administrator can reach.
- `ContractsRelationManager` row action → `RelationManager::getDefaultActionAuthorizationResponse()` denies it outright when `isReadOnly()` is true, *before* the policy runs. `ReadOnlyUnlessGrhAdmin` makes that true for everyone but administrators and `ROLE_GRH_ADMIN`.

So removing either piece leaves one of the two entry points open. Assert both with `assertActionVisible`/`assertActionHidden`; `ContractResourceTest` ("replicate action authorization") covers them.
