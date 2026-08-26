---
paths:
  - modules/Courrier/src/Filament/Resources/Inbox/Tables/InboxTables.php
---

# Tables

## The Inbox table is custom data: it owns its search, sort, pagination and caching
`->records()` gets none of Filament's query behaviour for free. Before `paginateRecords()` existed, `->searchable()`, `defaultSort('date')` and `->paginated([10,25,50])` were all declared and all inert — every message rendered on one page and the search box did nothing. Anything added to the columns has to be applied inside that closure.

Sort on `timestamp`, not `date`: the `date` key is formatted `d/m/Y H:i` for display and does not sort as a string.

Filament rebuilds a custom data source on *every* Livewire request, so opening the "Traiter" modal, filling it and submitting used to refetch the mailbox three times over. `getRecords()` caches the listing for `RECORDS_CACHE_TTL` seconds per mailbox. Anything that removes a message must call `forgetRecords()` then `$livewire->resetTable()` (`flushCachedTableRecords()` only clears the per-request cache), and the "Actualiser" header action exists so users can pull new mail in before the entry expires.
