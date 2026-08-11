<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The rename migrations used Blueprint::removeColumn(), which only strips a column from the
 * pending blueprint and never emits an `alter table ... drop column`, so the legacy `actif`
 * flags survived on already-migrated databases. Nothing reads them: recipients track state
 * through `receives_attachments`, and services have no active flag at all.
 */
return new class extends Migration
{
    protected $connection = 'maria-courrier';

    public function up(): void
    {
        foreach (['recipients', 'courrier_services'] as $table) {
            if (! Schema::connection($this->connection)->hasColumn($table, 'actif')) {
                continue;
            }

            Schema::connection($this->connection)->table($table, function (Blueprint $blueprint): void {
                $blueprint->dropColumn('actif');
            });
        }
    }
};
