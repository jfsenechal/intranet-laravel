# Publication Module

Publication management module for AcMarche Intranet.

## Database Structure

This module connects to the `publication` database with the following tables:

### publication
- `id` - Primary key
- `category_id` - Foreign key to category table
- `title` - Publication title
- `url` - Publication URL
- `expire_date` - Expiration date (nullable)
- `createdAt` - Creation timestamp
- `updatedAt` - Update timestamp

### category
- `id` - Primary key
- `name` - Category name
- `url` - Category URL (nullable)
- `wpCategoryId` - WordPress category ID

## Features

- **Publication Management**: Full CRUD operations for publications
- **Category Management**: Full CRUD operations for categories
- **Filament Integration**: Admin panel resources for both publications and categories
- **Database Connection**: Dedicated MariaDB connection for publications

## Installation

The module is already installed and configured. Database connection settings are in `.env`:

```env
DB_PUBLICATION_HOST=127.0.0.1
DB_PUBLICATION_PORT=3306
DB_PUBLICATION_DATABASE=publication
DB_PUBLICATION_USERNAME=root
DB_PUBLICATION_PASSWORD=homer
```

## Usage

### Accessing Admin Panel

Visit `/admin` and navigate to the "Content" navigation group to access:
- **Publications** - Manage publication entries
- **Categories** - Manage publication categories

### Models

```php
use AcMarche\Publication\Models\Publication;
use AcMarche\Publication\Models\Category;

// Get all publications
$publications = Publication::all();

// Get publications by category
$category = Category::find(1);
$publications = $category->publications;

// Create a new publication
$publication = Publication::create([
    'title' => 'My Publication',
    'url' => 'https://example.com',
    'category_id' => 1,
]);
```

## File Structure

```
modules/Publication/
├── config/
│   ├── database.php          # Database connection configuration
│   └── publication.php        # Module configuration
├── database/
│   └── migrations/
│       └── 2024_01_01_000001_create_publications_table.php
├── src/
│   ├── Filament/
│   │   └── Resources/
│   │       ├── CategoryResource.php
│   │       ├── CategoryResource/Pages/
│   │       ├── PublicationResource.php
│   │       └── PublicationResource/Pages/
│   ├── Models/
│   │   ├── Category.php
│   │   └── Publication.php
│   └── Providers/
│       └── PublicationServiceProvider.php
└── composer.json
```
