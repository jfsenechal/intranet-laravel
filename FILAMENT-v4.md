<!-- src https://laraveldaily.com/post/my-cursor-rules-for-laravel -->
## Filament Version 4 Rules

List of important changes in Filament v4 vs Filament v3:

- Validation rule `unique()` has `ignoreRecord: true` by default, no need to specify it.
- Don't use full namespaces when referencing Filament classes like `Filament\Forms\Components\DatePicker`. Always put the namespaces in `use` section on top and use only classname instead of full path.
- If you create custom Blade files with Tailwind classes, you need to create a custom theme and specify the folder of those Blade files in theme.css.
- Table Filters have `->schema()` instead of `->form()`
- `Action::make()` has `->schema()` instead of `->form()`
- Table has `->toolbarActions()` instead of `->bulkActions()`
