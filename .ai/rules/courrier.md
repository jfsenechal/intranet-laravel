---
paths:
  - 'modules/Courrier/**'
---

# Courrier

## Incoming mail: trust the attachment, not the email
Incoming mails reach the Inbox from a multifunction printer (MFP), so the envelope carries no usable information: the subject is a scanner-generated filename and the From is the copier.

Only the attachment holds the real content. Never seed form fields from `$record['subject']` (this prefill was removed from `InboxTables::recordActions()` and `InboxInfolist::getEmailViewSchema()`), and never feed the subject/body/sender to `IncomingMailAnalyzer` — it takes a file path only, on purpose.

## A draft incoming mail is hidden in three places, not one
`incoming_mails.is_draft` marks a mail the AI encoded from an Inbox attachment that no human has read yet. Nothing about the flag is enforced by the model: every read path has to exclude drafts itself, and there are three.

- `IncomingMailRepository::scopeToTodayForCurrentUser()` and `withoutCategory()` — the listings.
- `IncomingMailRepository::findByDateAndNotNotified()` and `getIncomingMailsForRecipient()` — the recipient notifications. A draft going out here mails unverified metadata to the whole commune.
- `IndexIncomingMailJob` — a draft is deleted from the index rather than indexed. Validating updates the record, which dispatches the job again and indexes it for real.

Add the same exclusion to any new query that lists or notifies mail.

`EditIncomingMail::validateDraft()` clears the flag from `afterSave()`, never inline: `EditRecord::save()` swallows a validation failure and returns, so clearing it next to the `save()` call would publish a mail the form rejected. The method checks `$this->record->is_draft` afterwards to decide whether to redirect.

## The CPAS reference lookup needs its composite index
`IncomingMail::nextCpasReferenceNumber()` does `max(CAST(reference_number AS SIGNED))` filtered on `department`. The cast makes the single-column `reference_number` index unusable, and there is no index on `department`, so the query full-scans `incoming_mails` — 1.9s against production's 166k rows. It runs on every "Traiter" modal a CPAS admin opens (InboxTables `fillForm`) and on every CPAS insert (the `creating` hook), so it is felt as "the modal is slow".

`incoming_mails_department_reference_number_index` on `(department, reference_number)` turns it into an index-only ref lookup: 1.9s → 8ms. Keep it. If the lookup is ever rewritten, keep the leading `department` column and read only `reference_number`, or the covering plan is lost.
