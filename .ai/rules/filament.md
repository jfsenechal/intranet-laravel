---
paths:
  - 'modules/**/src/Filament/**'
---

# Filament

## Never key() a Section that contains form fields
Calling `->key('x')` on a Section (or any layout component) prefixes the lookup key of every descendant field, so `assertFormFieldExists('reference_number')` starts failing with "field does not exist".

When you need a key only to target an action in tests via `TestAction::make(...)->schemaComponent('x')`, wrap just the action in a keyed `Group::make([...])->key('x')` instead of keying the enclosing section.

See `IncomingMailForm::getFieldsColumn()` (`ai-completion` group) for the pattern.

## Relation managers on a view page deny EditAction before the policy runs
The panel keeps Filament's `readOnlyRelationManagersOnResourceViewPagesByDefault()`, so `RelationManager::isReadOnly()` is true for any relation manager whose page class extends `ViewRecord`. Its authorization switch then returns `Response::deny()` for `EditAction`, `CreateAction`, `DeleteAction`, `AttachAction` and friends *before* consulting the policy — the button simply never renders, and the policy looks innocent.

Override `isReadOnly(): bool { return false; }` on the relation manager when its rows must stay actionable (see `Clients\RelationManagers\OrdersRelationManager` in modules/MealDelivery).

`assertActionHasUrl()` passes on a denied action: it resolves the action without checking visibility. Pair it with `assertActionVisible(TestAction::make('edit')->table($record))`, otherwise the test stays green while the UI shows nothing.
