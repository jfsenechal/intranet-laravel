---
paths:
  - 'modules/Hrm/**'
---

# Hrm

## Training hours quota excludes trainings without a certificate
A training whose `certificate_received` is false never counts towards an employee's hours quota (rule inherited from the previous intranet). `EmployeeInfolist::trainingGroups()` therefore returns two sums per type: `total` (certificates received, the quota) and `uncounted` (the rest, shown apart in `hrm::filament.employees.trainings`).

The `Total comptabilisé` summarizer on the Training list and relation tables follows the same rule via `TrainingTables::countedDurationSummarizer()` (id `counted_duration`): it sums only rows with `certificate_received = true`, on top of whatever filters are active.
