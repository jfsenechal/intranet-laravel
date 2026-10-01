---
paths:
  - 'modules/Hrm/src/Policies/**'
---

# Policies

## Direction heads read every contract of an employee they can view
`HrmAuthorization::canViewContract()` grants a `ROLE_GRH_DIRECTION` user a contract in two cases: the contract's `direction_id` is one of the directions they head, or `canViewEmployee()` already grants them the contract's employee (i.e. that employee has an *active* contract in their direction). So a director opening one of their agents also reads that agent's contracts belonging to other directions, closed or not — intentional, it mirrors `EmployeePolicy::view()`.

## Filament gates every resource page on `viewAny`
`Filament\Resources\Pages\Concerns\CanAuthorizeResourceAccess` aborts 403 on `Resource::canAccess()` → `canViewAny()` *before* a page checks the record, so a record-level `view()` grant alone never opens a view page. `ContractPolicy::viewAny()` therefore grants `hasAnyHrmRole()` and `ContractPolicy::scopeVisibleTo()` (applied in `ContractResource::getEloquentQuery()`) keeps the listing, counts and global search to the contracts the user may open — the same pairing as `TeleworkPolicy` and `EmployeePolicy`. Because the scoped query never resolves a foreign record, the view page of a contract outside the user's reach answers 404, not 403.

`AbsencePolicy` and `TrainingPolicy` still have the unfixed version of this problem: they grant `view()` to a direction head for their own agents while `viewAny()` is admin-only, so the Absence and Training view pages 403 when opened from the employee relation manager.
