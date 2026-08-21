---
paths:
  - 'modules/Courrier/src/Ai/**'
---

# Ai

## Rasterise the first page for the AI analysis, never attach the PDF
The reference number comes from the reception stamp inked on the paper before scanning, so no text pipeline sees it reliably.

Verified against real mails: sending the PDF with `Files\Document::fromPath()` returns an empty `reference_number` — the provider hands the model the extracted text only, where the stamp is missing or mangled. Rendering page 1 with `AttachmentOcr::renderFirstPage()` and sending it as `Files\Image` reads the stamp correctly (002686, 2693).

So `IncomingMailAnalyzer` always attaches the rendered page, on top of the extracted text. Do not "optimise" that image away for PDFs that have a text layer: the MFP writes searchable PDFs, so a text layer does not mean the document is born-digital.

## The sender is the author of the letter, not of the covering email
Incoming mail is often an internal email forwarding a citizen's or staff member's letter, with the letter itself scanned as page 1 and the email printout on a later page.

The expected `sender` is the letter's author. Confirmed by the user on 2026-08-19: for a handwritten leave request forwarded by the crèche, the sender is Sandrine Simon (the author), not Crèche Les Zoulous (the forwarder).

This is why the analysis leans on the page-1 image rather than the full extracted text, which mixes in the covering email. Do not "fix" the extraction back towards the forwarding service.

## Stamp services are resolved, never guessed
The reception stamp also names the destination services by their initials, sometimes several: "2693 - RH (CEE)" means RH and CEE.

The agent returns them raw in `services`; `ServiceRepository::findIdsByCodes()` maps them to rows and `AnalyzeAttachmentAction` fills `primary_services` with the result (only when the field is still empty).

A code is kept only when it matches exactly one service in the mail's department. Initials repeat both across departments (RH exists in Ville and Bgm) and inside one (MUS is the Musée and the Conservatoire), so an ambiguous code is dropped for the user to pick. Confirmed by the user on 2026-08-19, for MUS specifically: leave the field empty, they will choose. Never widen this to a LIKE search or a "best match": a wrong service silently misroutes the mail.

The model must not infer services from the letter's content either — only what the stamp shows.

One thing changed around this on 2026-08-21: a field the stamp left empty — because it named nothing, or because the code was ambiguous — is now filled from the routing retrieved from similar mail (see `.ai/rules/search.md`). The stamp still wins whenever it resolves; what the rule above forbids is *guessing at the stamp*, which is not what the retrieval does.

## The prompt leans on the NBN Z 01-002 letter layout
Belgian administrative mail is typed to NBN Z 01-002:2002 (classement et dactylographie des documents), so `IncomingMailAgent` describes that layout to help the model place each field: sender letterhead top-left, addressee block top-right (always the commune, never the sender), place and date top-right, "Objet"/"Concerne" for the description, signature block at the foot.

The rubric that matters most: **"Vos réf." is the addressee's reference — the commune's own — and "Nos réf." is the sender's.** Confirmed on a real SPW letter, where "Vos réf.: PU/2026/071" is the commune's permit file and "Nos réf.: F0510/…" is the SPW's. Neither is ever the reference number: that only comes from the stamp.

The prompt says explicitly that handwritten letters, forms, printed emails and invoices do not follow the norm, so the model falls back to the content. Keep that escape hatch if you edit this section — a good share of incoming mail is handwritten.
