---
paths:
  - 'modules/Courrier/src/Filament/Resources/IncomingMails/**'
---

# Incoming Mails

## The sender datalist ships in full — keep it filtered
`TextInput::datalist()` renders every option into the response, so the sender field's suggestions ride along inside the "Traiter" modal on every mount. Plucking `courrier_senders` whole cost a Ville admin 9 355 options / 402 KB of HTML per open.

Go through `SenderRepository::forDatalist()`, which keeps only the department's senders that appear on mail from the last two years (402 KB → 46 KB, and 0% loss of recent-mail coverage), cached per department and dropped by `Sender::saved`/`deleted`.

Do not swap the field for a `Select` to get server-side search: Filament applies an `in` rule to selects, and this field must stay free text — the AI autofill writes a sender name extracted from the PDF, and clerks encode senders that are not in the table.
