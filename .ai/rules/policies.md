---
paths:
  - 'modules/Hrm/src/Policies/**'
---

# Policies

## Direction heads read every contract of an employee they can view
`HrmAuthorization::canViewContract()` grants a `ROLE_GRH_DIRECTION` user a contract in two cases: the contract's `direction_id` is one of the directions they head, or `canViewEmployee()` already grants them the contract's employee (i.e. that employee has an *active* contract in their direction). So a director opening one of their agents also reads that agent's contracts belonging to other directions, closed or not — intentional, it mirrors `EmployeePolicy::view()`.

`ContractPolicy::viewAny()` stays admin-only: the standalone Contract resource is not exposed to direction heads, they reach contracts through the employee's relation manager.
