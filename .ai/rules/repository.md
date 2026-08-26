---
paths:
  - modules/Courrier/src/Repository/ImapRepository.php
---

# Repository

## Never list an IMAP mailbox with withBody()
The mailboxes hold scanned PDFs of several MB each, so `withBody()` over a listing downloads the whole mailbox (measured: 3.3s / 85MB peak for 19 CPAS messages) just to render subjects.

`getMessages()` fetches `withHeaders()->withBodyStructure()` instead: BODYSTRUCTURE describes the MIME parts without transferring them, which is enough for filename, content type and attachment count. Same for `findMessageByUid()`. Content is pulled one part at a time, on demand — `getMessageBody()` for the view modal, `getAttachment()` (lazy streams, byte-identical to the old parsed path) for the document.

`attachmentsOf()` branches on `Message` vs `FakeMessage`: only the concrete class takes `attachments(fetch: true)`, and `FakeMessage::bodyStructure()` is null unless a test passes one, so the fake parses its raw source instead.
