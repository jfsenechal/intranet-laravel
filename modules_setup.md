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

## Database Conventions

### Table Naming
All table names **MUST** follow Laravel conventions:
- **Plural form**: Use plural nouns (e.g., `users`, `documents`, `news_articles`)
- **Lowercase**: All lowercase letters (e.g., `categories`, not `Categories`)
- **Snake_case**: Use underscores for multi-word names (e.g., `blog_posts`, `user_profiles`)
- **Pivot tables**: Singular model names in alphabetical order (e.g., `document_user`, not `documents_users` or `user_document`)

**Examples:**
- ✅ `documents`
- ✅ `news_articles`
- ✅ `user_profiles`
- ✅ `document_category` (pivot table)
- ❌ `document` (not plural)
- ❌ `NewsArticles` (not lowercase)
- ❌ `news-articles` (not snake_case)

### Column Naming
All column names **MUST** be in English and follow these conventions:
- **Snake_case**: Use underscores for multi-word names (e.g., `created_at`, `file_path`)
- **Lowercase**: All lowercase letters
- **Descriptive**: Use clear, descriptive names (e.g., `publication_date`, not `pub_date`)
- **Foreign keys**: Follow the pattern `{model}_id` (e.g., `user_id`, `category_id`)

**Examples:**
- ✅ `title`, `description`, `created_at`
- ✅ `file_path`, `mime_type`, `user_id`
- ❌ `titre`, `description_fr` (French names)
- ❌ `filePath`, `mimeType` (camelCase)
- ❌ `CreatedAt` (not lowercase)

### Migrating French Columns to English

For existing modules or when creating new modules from French database schemas, you **MUST** create a migration to rename French columns to English equivalents.

#### Create Column Renaming Migration

```bash
# Create a new migration for renaming columns
php artisan make:migration rename_french_columns_in_table_name_table
```

#### Migration Template

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('table_name', function (Blueprint $table) {
            // Rename French columns to English
            $table->renameColumn('titre', 'title');
            $table->renameColumn('description', 'description'); // Already English
            $table->renameColumn('contenu', 'content');
            $table->renameColumn('date_publication', 'publication_date');
            $table->renameColumn('auteur_id', 'author_id');
            $table->renameColumn('categorie_id', 'category_id');
            $table->renameColumn('fichier', 'file_path');
            $table->renameColumn('type_mime', 'mime_type');
            $table->renameColumn('taille', 'file_size');
            $table->renameColumn('actif', 'is_active');
            $table->renameColumn('publie', 'is_published');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('table_name', function (Blueprint $table) {
            // Revert English columns back to French
            $table->renameColumn('title', 'titre');
            $table->renameColumn('description', 'description');
            $table->renameColumn('content', 'contenu');
            $table->renameColumn('publication_date', 'date_publication');
            $table->renameColumn('author_id', 'auteur_id');
            $table->renameColumn('category_id', 'categorie_id');
            $table->renameColumn('file_path', 'fichier');
            $table->renameColumn('mime_type', 'type_mime');
            $table->renameColumn('file_size', 'taille');
            $table->renameColumn('is_active', 'actif');
            $table->renameColumn('is_published', 'publie');
        });
    }
};
```

#### Common French to English Column Mappings

| French | English |
|--------|---------|
| `titre` | `title` |
| `nom` | `name` |
| `prenom` | `first_name` |
| `nom_famille` | `last_name` |
| `description` | `description` (same) |
| `contenu` | `content` |
| `texte` | `text` |
| `date_creation` | `created_at` |
| `date_modification` | `updated_at` |
| `date_publication` | `publication_date` |
| `auteur_id` | `author_id` |
| `utilisateur_id` | `user_id` |
| `categorie_id` | `category_id` |
| `fichier` | `file_path` |
| `nom_fichier` | `file_name` |
| `type_mime` | `mime_type` |
| `taille` | `file_size` |
| `taille_fichier` | `file_size` |
| `actif` | `is_active` |
| `publie` | `is_published` |
| `visible` | `is_visible` |
| `archive` | `is_archived` |
| `ordre` | `sort_order` |
| `position` | `position` (same) |

#### Important Notes

1. **Always include `down()` method**: This allows rolling back the migration if needed
2. **Test thoroughly**: Ensure all model relationships and queries are updated after renaming columns
3. **Update Model properties**: Update `$fillable`, `$casts`, and relationship methods in your models to match new column names
4. **Update existing queries**: Search for old column names in controllers, resources, and views
5. **Run migrations in order**: Column rename migrations should run before any migrations that reference the new column names

#### Model Updates After Column Renaming

After renaming columns, update your models:

```php
<?php

namespace AcMarche\ModuleName\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelName extends Model
{
    protected $fillable = [
        'title',        // was 'titre'
        'content',      // was 'contenu'
        'author_id',    // was 'auteur_id'
        'is_published', // was 'publie'
    ];

    protected function casts(): array
    {
        return [
            'publication_date' => 'datetime', // was 'date_publication'
            'is_published' => 'boolean',      // was 'publie'
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id'); // was 'auteur_id'
    }
}
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