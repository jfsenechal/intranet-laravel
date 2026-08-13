---
paths:
  - 'modules/*/src/Filament/Resources/**/Pages/*.php'
---

# Pages

## Never read the query string during a Filament page render
`request()->query(...)` is only populated on the initial page load. Livewire update requests (file uploads, live fields, save) POST to /livewire-*/update with no query string, so any `getTitle()` / `getHeaderActions()` / render-time helper that reads it gets null on every interaction.

Read the query string once in `mount()` and store it in a public Livewire property (it survives in the component snapshot); render-time code reads the property. Put guards like `abort_unless(...)` in `mount()`, never in `getTitle()` — an abort during a Livewire update returns a full HTML error page instead of a snapshot and the UI breaks (e.g. a 404 page replacing the form mid-upload).

See CreateOffense in modules/Offenses. modules/Hrm CreateAbsence still re-reads the query on render; its title just degrades instead of aborting.
