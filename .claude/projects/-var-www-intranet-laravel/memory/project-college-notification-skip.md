---
name: college-notification-skip
description: College notification mail intentionally skips recipients with no matching uploaded file
metadata:
  type: project
---

In `CreateNotification::sendNotifications()` (modules/College), a recipient flagged for a document type (collège/service) is **skipped** — receives no email — when the matching file was not uploaded.

**Why:** The user explicitly confirmed this is intended. It differs from the legacy `data/NotifierController.php`, which sent the email to every flagged recipient even when no matching attachment existed (producing attachment-less emails).

**How to apply:** Do not "restore" the legacy behavior by removing the `continue` that skips empty-attachment recipients. The skip is deliberate.
