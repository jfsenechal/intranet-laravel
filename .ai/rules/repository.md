---
paths:
  - modules/Courrier/src/Repository/ImapRepository.php
---

# Repository

## Never list an IMAP mailbox with withBody()
The mailboxes hold scanned PDFs of several MB each, so `withBody()` over a listing downloads the whole mailbox (measured: 3.3s / 85MB peak for 19 CPAS messages) just to render subjects.

`getMessages()` fetches `withHeaders()->withBodyStructure()` instead: BODYSTRUCTURE describes the MIME parts without transferring them, which is enough for filename, content type and attachment count. Same for `findMessageByUid()`. Content is pulled one part at a time, on demand — `getMessageBody()` for the view modal, `getAttachment()` (lazy streams, byte-identical to the old parsed path) for the document.

`attachmentsOf()` branches on `Message` vs `FakeMessage`: only the concrete class takes `attachments(fetch: true)`, and `FakeMessage::bodyStructure()` is null unless a test passes one, so the fake parses its raw source instead.

## Every mailbox command goes through onMailbox()
ImapEngine throws its own hierarchy (`DirectoryTree\ImapEngine\Exceptions\Exception` and children such as `ImapConnectionClosedException`), which no caller catches — they all `catch (ImapException)`. A server dropping the session during LOGIN therefore escaped as a Livewire 500 while the Inbox table rendered.

`connect()` now calls `Mailbox::connect()` eagerly (`Imap::mailbox()` only builds the object; the library connects lazily on the first command), and every command runs inside `onMailbox()`, the one place that translates an ImapEngine failure into `ImapException::operationFailed()`. Add new mailbox operations through that helper, never by touching `$this->mailbox` directly after `ensureConnected()`.
