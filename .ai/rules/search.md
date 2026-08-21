---
paths:
  - 'modules/Courrier/src/Search/**'
---

# Search

## Routing is retrieved from past mail, never reasoned from the letter
Where an incoming mail goes is not written on the paper. Measured over 16.000 recipient links since 2024, the surname of the person it was given to appears in the letter text only **13%** of the time. Do not try to prompt a model into reading it off the page — the information is not there.

It lives in the 166.000 courriers already encoded, which is what `SimilarMailFinder` queries: the letter's text goes to Meilisearch, and the services/recipients of the neighbours are tallied. Measured on 2026 mail, retrieving only from earlier mail in the same department:

- letter text + sender boost: recipient top-1 43%, top-5 67% (56% counting the 15% that return no neighbour)
- letter text alone: top-1 40%
- sender alone: top-1 21%

So the **text carries the signal, not the sender** — the reverse is true for services, where the sender alone is nearly as good (47% vs 50%), because a correspondent maps to a desk while a topic maps to a person.

The retrieval writes its result into the fields: `AnalyzeInboxMessagesJob` attaches it to the draft it creates, and `AnalyzeAttachmentAction` fills `primary_recipients` / `primary_services` in the form. Confirmed by the user on 2026-08-21, replacing the earlier design where the candidates were only grouped above the selects.

Three things hold it together, and all three must survive an edit here:

- **Only the top two per field** (`RoutingSuggestion::WRITTEN`). The ranking below that is made of alternatives, not of extra destinations, and every extra row is one the user deletes by hand.
- **Only into an empty field.** A routing the user picked outranks a retrieval, and so do the services read off the reception stamp — those are the mail room's own word, so `AnalyzeInboxMessagesJob::attachRouting()` skips the services when the stamp resolved any.
- **Only while it is a draft.** Nothing is written to a courrier a human has validated; there a retrieval could only offer to undo a decision someone took on purpose.

Autofill is defensible here only because nothing filled this way is visible until someone verifies it — a draft is out of the listing, the notifications and the index. Do not carry the behaviour over to a path that publishes directly.

`IndexIncomingMailJob` must also keep extracting `content` for drafts even though it does not index them, or there is nothing to query with while the draft is being verified.

Re-run the measurement with `scripts` like the scratch `knn.php` before changing the weights — the ablation is cheap and the intuitions here were wrong twice.
