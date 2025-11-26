# Module Setup Guide

This document outlines the architectural patterns and workflow for creating new modules in this Laravel intranet application.

## Module Architecture

Modules in this application follow a specific architectural pattern that separates concerns and keeps code organized and maintainable.

### Directory Structure

Each module follows this structure:

```
modules/ModuleName/
├── composer.json                          # Package definition
├── src/
│   ├── ModuleNameServiceProvider.php     # Service provider
│   ├── Models/                           # Eloquent models
│   │   └── ModelName.php
│   └── Filament/
│       └── Resources/
│           ├── ModelNameResource.php     # Main resource class (kept minimal)
│           └── ModelNameResource/
│               ├── Pages/                # Resource pages
│               │   ├── ListModelNames.php
│               │   ├── CreateModelName.php
│               │   ├── EditModelName.php
│               │   └── ViewModelName.php
│               ├── Schema/               # Form and Infolist configurations
│               │   ├── ModelNameForm.php
│               │   └── ModelNameInfolist.php
│               └── Tables/               # Table configurations
│                   └── ModelNameTables.php
├── database/
│   └── migrations/                       # Module migrations
├── resources/
│   └── views/                           # Module views (namespaced)
└── config/                               # Module configuration files
```

### Filament Resource Pattern

**IMPORTANT:** For all Filament resources, we use separate classes for table and form configurations instead of defining schemas directly in the Resource class.

#### Resource Class (Kept Minimal)

The main Resource class should be kept clean and minimal, delegating to specialized classes:

```php
<?php

namespace AcMarche\ModuleName\Filament\Resources;

use AcMarche\ModuleName\Filament\Resources\ModelNameResource\Pages;
use AcMarche\ModuleName\Filament\Resources\ModelNameResource\Schema\ModelNameForm;
use AcMarche\ModuleName\Filament\Resources\ModelNameResource\Tables\ModelNameTables;
use AcMarche\ModuleName\Models\ModelName;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ModelNameResource extends Resource
{
    protected static ?string $model = ModelName::class;
    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-document-text';
    }

    public static function getNavigationLabel(): string
    {
        return 'Model Names';
    }

    public static function form(Schema $schema): Schema
    {
        return ModelNameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModelNameTables::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListModelNames::route('/'),
            'create' => Pages\CreateModelName::route('/create'),
            'view' => Pages\ViewModelName::route('/{record}/view'),
            'edit' => Pages\EditModelName::route('/{record}/edit'),
        ];
    }
}
```

#### Form Configuration Class

Create a dedicated class for form schema in `Schema/ModelNameForm.php`:

```php
<?php

namespace AcMarche\ModuleName\Filament\Resources\ModelNameResource\Schema;

use Filament\Forms;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ModelNameForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make([
                    Forms\Components\TextInput::make('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\RichEditor::make('content')
                        ->label('Content')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->required(),
                ]),
            ]);
    }
}
```

#### Table Configuration Class

Create a dedicated class for table configuration in `Tables/ModelNameTables.php`:

```php
<?php

namespace AcMarche\ModuleName\Filament\Resources\ModelNameResource\Tables;

use AcMarche\ModuleName\Filament\Resources\ModelNameResource;
use AcMarche\ModuleName\Models\ModelName;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ModelNameTables
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->defaultPaginationPageOption(50)
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('Name')
                    ->url(fn(ModelName $record) => ModelNameResource::getUrl('view', ['record' => $record->id])),

                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->label('Category'),
            ])
            ->filters([
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name'),
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

#### Infolist Configuration Class (Optional)

If you need a custom view page, create an Infolist configuration in `Schema/ModelNameInfolist.php`:

```php
<?php

namespace AcMarche\ModuleName\Filament\Resources\ModelNameResource\Schema;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class ModelNameInfolist
{
    public static function configure(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make([
                    TextEntry::make('name')
                        ->label('Name'),

                    TextEntry::make('content')
                        ->html()
                        ->label('Content'),
                ]),
            ]);
    }
}
```

## Benefits of This Pattern

1. **Separation of Concerns**: Each class has a single, well-defined responsibility
2. **Maintainability**: Easy to find and modify table or form configurations
3. **Reusability**: Form or table configurations can be shared across different resource pages if needed
4. **Clean Resource Classes**: The main Resource class remains minimal and readable
5. **Organization**: Clear directory structure makes navigation intuitive

## Creating a New Module - Workflow

### 1. Create Module Directory Structure

```bash
mkdir -p modules/ModuleName/{src,database/migrations,resources/views,config}
mkdir -p modules/ModuleName/src/{Models,Filament/Resources}
```

### 2. Create composer.json

```json
{
    "name": "acmarche/module-name",
    "description": "Module description",
    "type": "library",
    "require": {
        "php": "^8.4"
    },
    "autoload": {
        "psr-4": {
            "AcMarche\\ModuleName\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "AcMarche\\ModuleName\\ModuleNameServiceProvider"
            ]
        }
    }
}
```

### 3. Create Service Provider

```php
<?php

namespace AcMarche\ModuleName;

use Illuminate\Support\ServiceProvider;

class ModuleNameServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views with namespace
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'module-name');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/module-name.php' => config_path('module-name.php'),
        ], 'module-name-config');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/module-name'),
        ], 'module-name-views');
    }
}
```

### 4. Create Model

```bash
# Create model in modules/ModuleName/src/Models/ModelName.php
```

### 5. Create Filament Resource with Separated Concerns

```bash
# Use Filament artisan commands to generate base files
php artisan make:filament-resource ModelName --generate --view
```

Then restructure following the pattern above:
- Move form schema to `Schema/ModelNameForm.php`
- Move table configuration to `Tables/ModelNameTables.php`
- Optionally create `Schema/ModelNameInfolist.php`
- Update Resource class to delegate to these classes

### 6. Create Migration

```bash
# Create migration in modules/ModuleName/database/migrations/
```

### 7. Add Module to Main composer.json

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "modules/ModuleName"
        }
    ],
    "require": {
        "acmarche/module-name": "@dev"
    }
}
```

### 8. Install Module

```bash
composer update acmarche/module-name
php artisan migrate
```

## Working with Modules

### Update Modules

```bash
composer update acmarche/document acmarche/news acmarche/module-name
```

### Run Migrations

```bash
php artisan migrate
```

### Publish Module Assets

```bash
# Publish config
php artisan vendor:publish --tag=module-name-config

# Publish views
php artisan vendor:publish --tag=module-name-views
```

## Naming Conventions

- **Namespace**: `AcMarche\ModuleName`
- **Package Name**: `acmarche/module-name` (kebab-case)
- **View Namespace**: `module-name` (kebab-case)
- **Form Class**: `{ModelName}Form` (e.g., `DocumentForm`)
- **Table Class**: `{ModelName}Tables` (e.g., `DocumentTables`)
- **Infolist Class**: `{ModelName}Infolist` (e.g., `DocumentInfolist`)

## Reference Modules

Use these existing modules as templates:
- **Document** (`acmarche/document`) - Complete example with forms, tables, and infolists
- **News** (`acmarche/news`) - News/blog management example

## Best Practices

1. **Always separate table and form configurations** into dedicated classes
2. **Keep Resource classes minimal** - only navigation, model binding, and delegation
3. **Follow existing naming conventions** from Document and News modules
4. **Use proper namespacing** with the `AcMarche` vendor namespace
5. **Create comprehensive migrations** for all model requirements
6. **Group related fields** in Sections for better UX
7. **Add appropriate validation** in form components
8. **Use relationships** where applicable instead of manual queries
9. **Test Filament resources** with feature tests for CRUD operations
10. **Document module-specific configuration** in publishable config files