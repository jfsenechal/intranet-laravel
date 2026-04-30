# AcMarche Document Module

Document management module for the AcMarche Laravel application.

## Features

- Create, read, update, and delete documents
- File upload support
- Document categorization
- Publish/unpublish functionality
- Soft deletes
- Filament admin panel integration

## Installation

This module is automatically loaded via the main application's composer.json.

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=document-config
```

## Migrations

Migrations are automatically loaded. Run:

```bash
php artisan migrate
```

## Usage

### Accessing the Admin Panel

Navigate to `/admin/documents` in your Filament admin panel.

### Model

```php
use AcMarche\Document\Models\Document;

$document = Document::create([
    'title' => 'My Document',
    'description' => 'Document description',
    'file_path' => 'documents/file.pdf',
    'file_name' => 'file.pdf',
    'is_published' => true,
]);
```

## Views

Publish views to customize:

```bash
php artisan vendor:publish --tag=document-views
```
