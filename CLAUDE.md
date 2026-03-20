# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 intranet application built with:
- Laravel 12 framework
- Livewire Volt for interactive components
- Tailwind CSS v4 for styling
- Pest v4 for testing
- SQLite database (default)
- Vite for asset bundling

## Key Commands

### Development
```bash
# Start full development environment (server, queue, logs, vite)
composer run dev

# Alternative: Start dev server with Vite
npm run dev
php artisan serve

# Build frontend assets
npm run build
```

### Testing
```bash
# Run all tests
php artisan test
# or
composer test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run tests matching a pattern
php artisan test --filter=testName
```

### Code Quality
```bash
# Format code with Laravel Pint (always run before committing)
vendor/bin/pint --dirty

# View logs
php artisan pail
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh database with seeders
php artisan migrate:fresh --seed
```

### Initial Setup
```bash
# Complete project setup (install dependencies, setup env, migrate, build assets)
composer run setup
```

## Architecture

### Laravel 12 Structure
This project uses Laravel 12's streamlined structure:
- **No `app/Console/Kernel.php`** - configuration is in `bootstrap/app.php` or `routes/console.php`
- **No middleware directory** - middleware registration happens in `bootstrap/app.php`
- **Commands auto-register** - files in `app/Console/Commands/` are automatically available
- **Service providers** - registered in `bootstrap/providers.php`

### Modular Architecture
This application uses a modular structure with separate packages:

#### Structure
- Modules are stored in `modules/` directory at root level
- Each module is an installable Composer package
- Modules use the `AcMarche` namespace (e.g., `AcMarche\Document`, `AcMarche\News`)
- Each module has its own:
  - `composer.json` - Package definition and dependencies
  - Service Provider - Auto-discovered by Laravel
  - Models - With proper namespacing
  - Migrations - Automatically loaded
  - Views - Namespaced (e.g., `document::view-name`)
  - Configuration - Publishable config files
  - Filament Resources - Admin panel integration

#### Available Modules
- **Document** (`acmarche/document`) - Document management with file uploads
- **News** (`acmarche/news`) - News/blog management with rich content

#### Creating a New Module
1. Create directory structure: `modules/YourModule/{src,database/migrations,resources/views,config}`
2. Create `composer.json` with proper namespacing (`AcMarche\YourModule`)
3. Create Service Provider extending `Illuminate\Support\ServiceProvider`
4. Create Filament Resources in `src/Filament/Resources/`
5. Add module to main `composer.json` repositories and require sections
6. Run `composer update` to install the module
7. Run migrations with `php artisan migrate`

#### Module Service Providers
- Auto-discovered via Laravel's package discovery
- Load migrations from module's `database/migrations/`
- Load views with namespace from module's `resources/views/`
- Register publishable assets (configs, views, migrations)

#### Working with Modules
```bash
# Install/update modules
composer update acmarche/document acmarche/news

# Run module migrations
php artisan migrate

# Publish module config
php artisan vendor:publish --tag=document-config
php artisan vendor:publish --tag=news-config

# Publish module views
php artisan vendor:publish --tag=document-views
php artisan vendor:publish --tag=news-views
```

### Livewire Volt Configuration
Volt components are mounted from two directories (configured in `VoltServiceProvider`):
- `resources/views/livewire/` - traditional Livewire components path
- `resources/views/pages/` - page-level Volt components

Check existing Volt components to determine if this project uses functional or class-based Volt syntax before creating new ones.

### Filament Admin Panel
This application uses Filament v4 for admin panel functionality:
- **Admin URL**: `/admin` (default Filament route)
- **Module Integration**: Each module has Filament Resources in `src/Filament/Resources/`
- **Resources**: Admin interfaces for CRUD operations on module models
- **Navigation Groups**: Resources are organized by groups (e.g., "Content")

#### Creating Filament Resources for Modules
When adding a new module, create Filament resources following this structure:
```
modules/YourModule/src/Filament/Resources/
├── YourResource.php
└── YourResource/
    └── Pages/
        ├── ListYours.php
        ├── CreateYour.php
        └── EditYour.php
```

Use the existing Document and News modules as templates for proper integration.

### Frontend Stack
- **Vite** configuration includes Tailwind CSS v4 plugin and Laravel plugin
- **Tailwind CSS v4** uses CSS-first configuration with `@theme` directive in `resources/css/app.css`
- **CSS sources** defined via `@source` directive include `../views` and pagination views
- **Custom fonts** - Instrument Sans is the primary font family
- Assets: `resources/css/app.css` and `resources/js/app.js`

### Testing Setup
- Tests use in-memory SQLite database
- PHPUnit configuration disables various services in test environment (Pulse, Telescope, Nightwatch)
- All tests written in Pest syntax
- Tests are organized in `tests/Feature/` and `tests/Unit/`

### Database
- Default connection: SQLite
- Default drivers: database-backed sessions, cache, and queues
- Migrations located in `database/migrations/`

## Development Workflow

### Creating New Features
1. Use appropriate `php artisan make:` commands with `--no-interaction` flag
2. For models: consider creating factories and seeders simultaneously
3. For validation: always create Form Request classes, never inline validation
4. For Livewire components: use `php artisan make:livewire [name]`
5. For Volt components: use `php artisan make:volt [name] [--test] [--pest]`

### Before Committing
1. Run `vendor/bin/pint --dirty` to format code
2. Run relevant tests with `php artisan test --filter=relevantTest`
3. Consider running full test suite

### Frontend Changes
If frontend changes don't appear:
- Run `npm run build` for production build
- Or ask user to run `npm run dev` or `composer run dev` for development

## Important Conventions

### Models & Database
- Use Eloquent relationships with proper type hints
- Leverage eager loading to prevent N+1 queries
- Model casts should use the `casts()` method (check existing models for convention)
- When modifying columns in migrations, include ALL previous attributes to avoid data loss

### PHP Standards
- Always use explicit return type declarations
- Use PHP 8 constructor property promotion
- Use curly braces for all control structures
- Prefer PHPDoc blocks over inline comments
- Use descriptive names (e.g., `isRegisteredForDiscounts` not `discount()`)

### Routing & URLs
- Prefer named routes and `route()` function for URL generation
- Never use `env()` outside config files - always use `config()`

### Testing
- Use model factories for test data
- Check for factory states before manual setup
- Most tests should be feature tests, not unit tests
- Use `RefreshDatabase` trait when needed

### Configuration
- Environment variables only in config files
- Use `config('key')` not `env('KEY')` in application code

## Laravel Boost MCP Tools

This project has Laravel Boost installed, which provides powerful MCP tools:
- `application-info` - Get comprehensive app information including packages and versions
- `search-docs` - Search version-specific Laravel ecosystem documentation
- `database-query` - Execute read-only SQL queries
- `database-schema` - View complete database schema
- `list-routes` - List all application routes
- `list-artisan-commands` - List available Artisan commands
- `tinker` - Execute PHP code in Laravel context
- `read-log-entries` - Read application logs
- `browser-logs` - Read browser console logs
- `last-error` - Get details of the last error/exception
- `get-absolute-url` - Generate absolute URLs for paths or routes

Use these tools extensively for debugging and understanding the application.

===

<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- filament/filament (FILAMENT) - v5
- laravel/framework (LARAVEL) - v12
- laravel/prompts (PROMPTS) - v0
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- rector/rector (RECTOR) - v2
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

- Laravel Boost is an MCP server that comes with powerful tools designed specifically for this application. Use them.

## Artisan Commands

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`, `php artisan tinker --execute "..."`).
- Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.

## URLs

- Whenever you share a project URL with the user, you should use the `get-absolute-url` tool to ensure you're using the correct scheme, domain/IP, and port.

## Debugging

- Use the `database-query` tool when you only need to read from the database.
- Use the `database-schema` tool to inspect table structure before writing migrations or models.
- To execute PHP code for debugging, run `php artisan tinker --execute "your code here"` directly.
- To read configuration values, read the config files directly or run `php artisan config:show [key]`.
- To inspect routes, run `php artisan route:list` directly.
- To check environment variables, read the `.env` file directly.

## Reading Browser Logs With the `browser-logs` Tool

- You can read browser logs, errors, and exceptions using the `browser-logs` tool from Boost.
- Only recent browser logs will be useful - ignore old logs.

## Searching Documentation (Critically Important)

- Boost comes with a powerful `search-docs` tool you should use before trying other approaches when working with Laravel or Laravel ecosystem packages. This tool automatically passes a list of installed packages and their versions to the remote Boost API, so it returns only version-specific documentation for the user's circumstance. You should pass an array of packages to filter on if you know you need docs for particular packages.
- Search the documentation before making code changes to ensure we are taking the correct approach.
- Use multiple, broad, simple, topic-based queries at once. For example: `['rate limiting', 'routing rate limiting', 'routing']`. The most relevant results will be returned first.
- Do not add package names to queries; package information is already shared. For example, use `test resource table`, not `filament 4 test resource table`.

### Available Search Syntax

1. Simple Word Searches with auto-stemming - query=authentication - finds 'authenticate' and 'auth'.
2. Multiple Words (AND Logic) - query=rate limit - finds knowledge containing both "rate" AND "limit".
3. Quoted Phrases (Exact Position) - query="infinite scroll" - words must be adjacent and in that order.
4. Mixed Queries - query=middleware "rate limit" - "middleware" AND exact phrase "rate limit".
5. Multiple Queries - queries=["authentication", "middleware"] - ANY of these terms.

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.

## Constructors

- Use PHP 8 constructor property promotion in `__construct()`.
    - `public function __construct(public GitHub $github) { }`
- Do not allow empty `__construct()` methods with zero parameters unless the constructor is private.

## Type Declarations

- Always use explicit return type declarations for methods and functions.
- Use appropriate PHP type hints for method parameters.

<!-- Explicit Return Types and Method Params -->
```php
protected function isAccessible(User $user, ?string $path = null): bool
{
    ...
}
```

## Enums

- Typically, keys in an Enum should be TitleCase. For example: `FavoritePerson`, `BestLake`, `Monthly`.

## Comments

- Prefer PHPDoc blocks over inline comments. Never use comments within the code itself unless the logic is exceptionally complex.

## PHPDoc Blocks

- Add useful array shape type definitions when appropriate.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

## Database

- Always use proper Eloquent relationship methods with return type hints. Prefer relationship methods over raw queries or manual joins.
- Use Eloquent models and relationships before suggesting raw database queries.
- Avoid `DB::`; prefer `Model::query()`. Generate code that leverages Laravel's ORM capabilities rather than bypassing them.
- Generate code that prevents N+1 query problems by using eager loading.
- Use Laravel's query builder for very complex database operations.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

### APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## Controllers & Validation

- Always create Form Request classes for validation rather than inline validation in controllers. Include both validation rules and custom error messages.
- Check sibling Form Requests to see if the application uses array or string based validation rules.

## Authentication & Authorization

- Use Laravel's built-in authentication and authorization features (gates, policies, Sanctum, etc.).

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Queues

- Use queued jobs for time-consuming operations with the `ShouldQueue` interface.

## Configuration

- Use environment variables only in configuration files - never use the `env()` function directly outside of config files. Always use `config('app.name')`, not `env('APP_NAME')`.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== laravel/v12 rules ===

# Laravel 12

- CRITICAL: ALWAYS use `search-docs` tool for version-specific Laravel documentation and updated code examples.
- Since Laravel 11, Laravel has a new streamlined file structure which this project uses.

## Laravel 12 Structure

- In Laravel 12, middleware are no longer registered in `app/Http/Kernel.php`.
- Middleware are configured declaratively in `bootstrap/app.php` using `Application::configure()->withMiddleware()`.
- `bootstrap/app.php` is the file to register middleware, exceptions, and routing files.
- `bootstrap/providers.php` contains application specific service providers.
- The `app/Console/Kernel.php` file no longer exists; use `bootstrap/app.php` or `routes/console.php` for console configuration.
- Console commands in `app/Console/Commands/` are automatically available and do not require manual registration.

## Database

- When modifying a column, the migration must include all of the attributes that were previously defined on the column. Otherwise, they will be dropped and lost.
- Laravel 12 allows limiting eagerly loaded records natively, without external packages: `$query->latest()->limit(10);`.

### Models

- Casts can and likely should be set in a `casts()` method on a model rather than the `$casts` property. Follow existing conventions from other models.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== filament/filament rules ===

## Filament

- Filament is used by this application. Follow the existing conventions for how and where it is implemented.
- Filament is a Server-Driven UI (SDUI) framework for Laravel that lets you define user interfaces in PHP using structured configuration objects. Built on Livewire, Alpine.js, and Tailwind CSS.
- Use the `search-docs` tool for official documentation on Artisan commands, code examples, testing, relationships, and idiomatic practices. If `search-docs` is unavailable, refer to https://filamentphp.com/docs.

### Artisan

- Always use Filament-specific Artisan commands to create files. Find available commands with the `list-artisan-commands` tool, or run `php artisan --help`.
- Always inspect required options before running a command, and always pass `--no-interaction`.

### Patterns

Always use static `make()` methods to initialize components. Most configuration methods accept a `Closure` for dynamic values.

Use `Get $get` to read other form field values for conditional logic:

<code-snippet name="Conditional form field visibility" lang="php">
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;

Select::make('type')
    ->options(CompanyType::class)
    ->required()
    ->live(),

TextInput::make('company_name')
    ->required()
    ->visible(fn (Get $get): bool => $get('type') === 'business'),

</code-snippet>

Use `state()` with a `Closure` to compute derived column values:

<code-snippet name="Computed table column value" lang="php">
use Filament\Tables\Columns\TextColumn;

TextColumn::make('full_name')
    ->state(fn (User $record): string => "{$record->first_name} {$record->last_name}"),

</code-snippet>

Actions encapsulate a button with an optional modal form and logic:

<code-snippet name="Action with modal form" lang="php">
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;

Action::make('updateEmail')
    ->schema([
        TextInput::make('email')
            ->email()
            ->required(),
    ])
    ->action(fn (array $data, User $record) => $record->update($data))

</code-snippet>

### Testing

Always authenticate before testing panel functionality. Filament uses Livewire, so use `Livewire::test()` or `livewire()` (available when `pestphp/pest-plugin-livewire` is in `composer.json`):

<code-snippet name="Table test" lang="php">
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->assertCanSeeTableRecords($users)
    ->searchTable($users->first()->name)
    ->assertCanSeeTableRecords($users->take(1))
    ->assertCanNotSeeTableRecords($users->skip(1));

</code-snippet>

<code-snippet name="Create resource test" lang="php">
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => 'Test',
        'email' => 'test@example.com',
    ])
    ->call('create')
    ->assertNotified()
    ->assertRedirect();

assertDatabaseHas(User::class, [
    'name' => 'Test',
    'email' => 'test@example.com',
]);

</code-snippet>

<code-snippet name="Testing validation" lang="php">
use function Pest\Livewire\livewire;

livewire(CreateUser::class)
    ->fillForm([
        'name' => null,
        'email' => 'invalid-email',
    ])
    ->call('create')
    ->assertHasFormErrors([
        'name' => 'required',
        'email' => 'email',
    ])
    ->assertNotNotified();

</code-snippet>

<code-snippet name="Calling actions in pages" lang="php">
use Filament\Actions\DeleteAction;
use function Pest\Livewire\livewire;

livewire(EditUser::class, ['record' => $user->id])
    ->callAction(DeleteAction::class)
    ->assertNotified()
    ->assertRedirect();

</code-snippet>

<code-snippet name="Calling actions in tables" lang="php">
use Filament\Actions\Testing\TestAction;
use function Pest\Livewire\livewire;

livewire(ListUsers::class)
    ->callAction(TestAction::make('promote')->table($user), [
        'role' => 'admin',
    ])
    ->assertNotified();

</code-snippet>

### Correct Namespaces

- Form fields (`TextInput`, `Select`, etc.): `Filament\Forms\Components\`
- Infolist entries (`TextEntry`, `IconEntry`, etc.): `Filament\Infolists\Components\`
- Layout components (`Grid`, `Section`, `Fieldset`, `Tabs`, `Wizard`, etc.): `Filament\Schemas\Components\`
- Schema utilities (`Get`, `Set`, etc.): `Filament\Schemas\Components\Utilities\`
- Actions (`DeleteAction`, `CreateAction`, etc.): `Filament\Actions\`. Never use `Filament\Tables\Actions\`, `Filament\Forms\Actions\`, or any other sub-namespace for actions.
- Icons: `Filament\Support\Icons\Heroicon` enum (e.g., `Heroicon::PencilSquare`)

### Common Mistakes

- **Never assume public file visibility.** File visibility is `private` by default. Always use `->visibility('public')` when public access is needed.
- **Never assume full-width layout.** `Grid`, `Section`, and `Fieldset` do not span all columns by default. Explicitly set column spans when needed.

=== filament/blueprint rules ===

## Filament Blueprint

You are writing Filament v5 implementation plans. Plans must be specific enough
that an implementing agent can write code without making decisions.

**Start here**: Read
`/vendor/filament/blueprint/resources/markdown/planning/overview.md` for plan format,
required sections, and what to clarify with the user before planning.

</laravel-boost-guidelines>
