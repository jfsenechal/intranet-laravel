<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Two leftovers made `aldermen_recipients` reject every insert, both `not null` without a default
 * and neither of them written by the module:
 *
 * - `slugname`: `2026_08_11_144608_drop_legacy_slug_columns` assumed the legacy recipients column
 *   was named `slug`, but the `destinataires` table it was renamed from calls it `slugname`, just
 *   like `events` did, so that migration dropped nothing on the recipients table in production.
 * - `token`: created only by the fresh-install branch of the create migration and never read
 *   anywhere, so it broke recipient creation on every freshly installed database.
 */
return new class extends Migration
{
    protected $connection = 'maria-aldermen-agenda';

    public function up(): void
    {
        foreach (['slugname', 'token'] as $column) {
            if (! Schema::connection($this->connection)->hasColumn('aldermen_recipients', $column)) {
                continue;
            }

            Schema::connection($this->connection)->table(
                'aldermen_recipients',
                function (Blueprint $table) use ($column): void {
                    $table->dropColumn($column);
                }
            );
        }
    }
};
