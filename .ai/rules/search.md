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

Two consequences to keep: the result is a suggestion shown above a select, never a value written into a field (43% is nowhere near autofill); and `IndexIncomingMailJob` must keep extracting `content` for drafts even though it does not index them, or there is nothing to query with while the draft is being verified.

Re-run the measurement with `scripts` like the scratch `knn.php` before changing the weights — the ablation is cheap and the intuitions here were wrong twice.
