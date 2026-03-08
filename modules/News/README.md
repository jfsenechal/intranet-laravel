# AcMarche News Module

News management module for the AcMarche Laravel application.

## Features

- Create, read, update, and delete news articles
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
php artisan vendor:publish --tag=news-config
```

## Migrations

Migrations are automatically loaded. Run:

```bash
php artisan migrate
```

## Usage

### Accessing the Admin Panel

Navigate to `/admin/news` in your Filament admin panel.

### Model

```php
use AcMarche\News\Models\News;

$news = News::create([
    'title' => 'Breaking News',
    'slug' => 'breaking-news',
    'content' => 'News content here...',
    'is_published' => true,
    'is_featured' => true,
]);
```

## Views

Publish views to customize:

```bash
php artisan vendor:publish --tag=news-views
```
