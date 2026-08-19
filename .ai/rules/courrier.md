---
paths:
  - 'modules/Courrier/**'
---

# Courrier

## Incoming mail: trust the attachment, not the email
Incoming mails reach the Inbox from a multifunction printer (MFP), so the envelope carries no usable information: the subject is a scanner-generated filename and the From is the copier.

Only the attachment holds the real content. Never seed form fields from `$record['subject']` (this prefill was removed from `InboxTables::recordActions()` and `InboxInfolist::getEmailViewSchema()`), and never feed the subject/body/sender to `IncomingMailAnalyzer` — it takes a file path only, on purpose.
