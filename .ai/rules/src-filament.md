---
paths:
  - 'modules/Hrm/src/Filament/**'
---

# Src Filament

## XLSX exports: read the function from active contracts, and use getTableQueryForExport()
`employees.job_title` is deprecated (see `Employee::DEPRECATED_JOB_TITLE`): half the rows are empty and the rest hold stale values. An agent's function comes from `activeContracts->pluck('job_title')`, like the `active_functions` table column and `EmployeeDirectoryExport`. Never put `$employee->job_title` in an export or an infolist.

`getFilteredTableQuery()` applies filters and search but NOT sorting, so an export built on it ignores the sort the user set on screen. Every Hrm list page now passes `getTableQueryForExport()`; keep it that way for new exports.

Assert the order a new export produces by going through the action, not by calling `getTableQueryForExport()` yourself — the latter passes even when the page is wired to the wrong query. Livewire captures the returned `StreamedResponse` into the `download` effect, so `xlsxRows(base64_decode(data_get($component->effects, 'download.content')))` (helper in `tests/Pest.php`) reads the file back.

When an export walks the query with `lazy()`, append the primary key to the sort: `lazy()` pages by offset, and a non-unique sort column (`last_name`) lets rows repeat or vanish between two chunks. See `EmployeeExport::rowsQuery()`.

`EmployerFilter::makeThrough($relation, activeOnly: true)` is required on listings of *people* (employee list), otherwise an agent who once held a CPAS contract still matches the CPAS filter while working for the Ville. Leave `activeOnly` off on listings of past records (absences, trainings), where the employer of the time is what matters.
