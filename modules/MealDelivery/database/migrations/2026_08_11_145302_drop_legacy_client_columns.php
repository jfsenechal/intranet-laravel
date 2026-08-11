<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The rename migration used Blueprint::removeColumn(), which only strips a column from the
 * pending blueprint and never emits an `alter table ... drop column`, so the legacy columns
 * survived on already-migrated databases.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private const LEGACY_COLUMNS = ['slugname', 'tournee_save'];

    protected $connection = 'maria-meal-delivery';

    public function up(): void
    {
        $columns = array_values(array_filter(
            self::LEGACY_COLUMNS,
            fn (string $column): bool => Schema::connection($this->connection)->hasColumn('clients', $column),
        ));

        if ($columns === []) {
            return;
        }

        Schema::connection($this->connection)->table('clients', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
