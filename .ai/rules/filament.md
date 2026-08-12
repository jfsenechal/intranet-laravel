---
paths:
  - 'modules/**/src/Filament/**'
---

# Filament

## Never key() a Section that contains form fields
Calling `->key('x')` on a Section (or any layout component) prefixes the lookup key of every descendant field, so `assertFormFieldExists('reference_number')` starts failing with "field does not exist".

When you need a key only to target an action in tests via `TestAction::make(...)->schemaComponent('x')`, wrap just the action in a keyed `Group::make([...])->key('x')` instead of keying the enclosing section.

See `IncomingMailForm::getFieldsColumn()` (`ai-completion` group) for the pattern.
