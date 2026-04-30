# Modular Architecture Setup Guide

## Overview

The application now has a modular architecture where each module is a separate Composer package. This document explains the structure and how to work with modules.

## Module Structure

Each module is located in `modules/` directory and follows this structure:

```
modules/
├── Document/
│   ├── composer.json
│   ├── README.md
│   ├── config/
│   │   └── document.php
│   ├── database/
│   │   └── migrations/
│   │       └── 2024_01_01_000001_create_documents_table.php
│   ├── resources/
│   │   └── views/
│   └── src/
│       ├── DocumentServiceProvider.php
│       ├── Models/
│       │   └── Document.php
│       └── Filament/
│           └── Resources/
│               ├── DocumentResource.php
│               └── DocumentResource/
│                   └── Pages/
│                       ├── ListDocuments.php
│                       ├── CreateDocument.php
│                       └── EditDocument.php
└── News/
    └── [similar structure]
```

## Current Modules

### 1. Document Module (`acmarche/document`)
- **Namespace**: `AcMarche\Document`
- **Purpose**: Document management with file uploads
- **Features**:
  - Document CRUD operations
  - File upload support
  - Categories
  - Publish/unpublish functionality
  - Soft deletes

### 2. News Module (`acmarche/news`)
- **Namespace**: `AcMarche\News`
- **Purpose**: News/blog management
- **Features**:
  - News article CRUD operations
  - Rich text editor
  - Featured images
  - Categories
  - Featured articles flag
  - SEO-friendly slugs
  - Soft deletes

## Installation Status

✅ Modules created and structured
✅ Composer repositories configured
✅ Modules installed via Composer (as symlinks)
✅ Migrations created and run successfully
✅ Service providers auto-discovered by Laravel
✅ CLAUDE.md documentation updated

⚠️ **Filament Integration Pending**: The Filament resources need to be updated to match Filament v4's latest API changes. The resources are created but commented out in `AdminPanelProvider.php` pending updates.

## Working with Modules

### Installing/Updating Modules

```bash
# Update all modules
composer update acmarche/document acmarche/news

# Update specific module
composer update acmarche/document
```

### Running Migrations

```bash
# Run all migrations (including module migrations)
php artisan migrate

# Rollback
php artisan migrate:rollback
```

### Publishing Module Assets

```bash
# Publish module configuration
php artisan vendor:publish --tag=document-config
php artisan vendor:publish --tag=news-config

# Publish module views
php artisan vendor:publish --tag=document-views
php artisan vendor:publish --tag=news-views

# Publish migrations (if needed)
php artisan vendor:publish --tag=document-migrations
php artisan vendor:publish --tag=news-migrations
```

### Using Module Models

```php
use AcMarche\Document\Models\Document;
use AcMarche\News\Models\News;

// Create a document
$document = Document::create([
    'title' => 'My Document',
    'description' => 'Document description',
    'file_path' => 'documents/file.pdf',
    'file_name' => 'file.pdf',
    'is_published' => true,
]);

// Create a news article
$news = News::create([
    'title' => 'Breaking News',
    'slug' => 'breaking-news',
    'content' => 'News content...',
    'is_published' => true,
]);
```

### Using Module Views

```blade
{{-- Using namespaced views --}}
@include('document::partials.header')
@include('news::partials.article-card')
```

## Creating a New Module

### Step 1: Create Directory Structure

```bash
mkdir -p modules/YourModule/{src/{Models,Filament/Resources},database/migrations,resources/views,config}
```

### Step 2: Create composer.json

Create `modules/YourModule/composer.json`:

```json
{
    "name": "acmarche/yourmodule",
    "description": "Your module description",
    "type": "library",
    "license": "MIT",
    "require": {
        "php": "^8.3",
        "filament/filament": "^4.0",
        "illuminate/contracts": "^12.0"
    },
    "autoload": {
        "psr-4": {
            "AcMarche\\YourModule\\": "src/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "AcMarche\\YourModule\\YourModuleServiceProvider"
            ]
        }
    }
}
```

### Step 3: Create Service Provider

Create `modules/YourModule/src/YourModuleServiceProvider.php`:

```php
<?php

declare(strict_types=1);

namespace AcMarche\YourModule;

use Illuminate\Support\ServiceProvider;

class YourModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/yourmodule.php',
            'yourmodule'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'yourmodule');

        $this->publishes([
            __DIR__.'/../config/yourmodule.php' => config_path('yourmodule.php'),
        ], 'yourmodule-config');
    }
}
```

### Step 4: Register Module in Main composer.json

Add to `composer.json`:

```json
{
    "require": {
        "acmarche/yourmodule": "*@dev"
    },
    "repositories": [
        {
            "type": "path",
            "url": "./modules/YourModule"
        }
    ]
}
```

### Step 5: Install the Module

```bash
composer update acmarche/yourmodule
php artisan migrate
```

## Filament Integration

### Current Issue

The Filament resources were created using older Filament v4 API. Recent Filament v4 versions have changed the method signatures and property declarations.

### To Fix Filament Resources

1. Update the resources to use Filament v4's latest `Schema` API instead of `Form` and `Table` classes
2. Use static methods instead of properties for navigation settings
3. Uncomment the resource discovery in `app/Providers/Filament/AdminPanelProvider.php`

Example (needs updating based on latest Filament docs):

```php
// Old way (current in modules)
public static function form(Form $form): Form { ... }

// New way (needs implementation)
public static function form(Schema $schema): Schema { ... }
```

### Temporary Workaround

The Filament resource discovery is currently commented out in `AdminPanelProvider.php`. The modules work perfectly for:
- Models and database operations
- Views
- Configuration
- Migrations
- Service providers

Only the Filament admin interface needs to be updated.

## Module Benefits

1. **Separation of Concerns**: Each module is self-contained
2. **Reusability**: Modules can be used in other projects
3. **Independent Testing**: Each module can have its own tests
4. **Version Control**: Modules can be versioned independently
5. **Team Development**: Different teams can work on different modules
6. **Easy Maintenance**: Changes to one module don't affect others

## Database Schema

### Documents Table
- id
- title
- description
- file_path
- file_name
- file_size
- mime_type
- category
- is_published
- published_at
- timestamps
- soft deletes

### News Table
- id
- title
- slug (unique)
- excerpt
- content
- featured_image
- author
- category
- is_published
- is_featured
- published_at
- timestamps
- soft deletes

## Next Steps

1. Update Filament resources to match latest Filament v4 API
2. Create tests for each module
3. Add factories and seeders
4. Create frontend views for displaying content
5. Add policies for authorization
6. Consider adding more modules as needed

## Support

For module-specific documentation, see:
- `modules/Document/README.md`
- `modules/News/README.md`

For Filament documentation:
- Use `search-docs` MCP tool with package filter for `filament/filament`
- Visit https://filamentphp.com/docs
