# AcMarche Ad Module

Ad management module for the AcMarche Laravel application.

## Features

- Create, read, update, and delete ad articles
- Rich text editor for content
- Featured image support
- Article categorization
- Featured articles
- Publish/unpublish functionality
- SEO-friendly slugs
- Soft deletes
- Filament admin panel integration

## Installation

This module is automatically loaded via the main application's composer.json.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=ad-config
```

## Migrations

Migrations are automatically loaded. Run:

```bash
php artisan migrate
```

## Usage

### Accessing the Admin Panel

Navigate to `/admin/ad` in your Filament admin panel.

### Model

```php
use AcMarche\Ad\Models\Ad;

$classifiedAd = Ad::create([
    'title' => 'Breaking Ad',
    'slug' => 'breaking-ad',
    'content' => 'Ad content here...',
    'is_published' => true,
    'is_featured' => true,
]);
```

## Views

Publish views to customize:

```bash
php artisan vendor:publish --tag=ad-views
```
