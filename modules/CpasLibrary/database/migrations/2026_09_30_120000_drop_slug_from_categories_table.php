<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration
{
    protected $connection = 'maria-cpas-library';

    public function up(): void
    {
        $schema = Schema::connection('maria-cpas-library');

        if (! $schema->hasColumn('categories', 'slug')) {
            return;
        }

        // The unique index is named by Doctrine on the legacy table and by Laravel on fresh installs.
        $slugIndexes = collect($schema->getIndexes('categories'))
            ->filter(fn (array $index): bool => $index['columns'] === ['slug'] && ! $index['primary'])
            ->pluck('name');

        $schema->table('categories', function (Blueprint $table) use ($slugIndexes): void {
            foreach ($slugIndexes as $indexName) {
                $table->dropIndex($indexName);
            }
            $table->dropColumn('slug');
        });
    }

    public function down(): void
    {
        Schema::connection('maria-cpas-library')->table('categories', function (Blueprint $table): void {
            $table->string('slug', 255)->nullable()->unique()->after('description');
        });
    }
};
