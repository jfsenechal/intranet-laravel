<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The legacy rename migrations used `Blueprint::removeColumn()`, which only strips a column
 * from the pending blueprint definition and never emits an `alter table ... drop column`.
 * The legacy `events.slugname` and `aldermen_recipients.slug` columns therefore survived on
 * already-migrated databases, and `slugname` being `not null` without a default broke every
 * event creation.
 */
return new class extends Migration
{
    protected $connection = 'maria-aldermen-agenda';

    public function up(): void
    {
        if (Schema::connection($this->connection)->hasColumn('events', 'slugname')) {
            Schema::connection($this->connection)->table('events', function (Blueprint $table): void {
                $table->dropColumn('slugname');
            });
        }

        if (Schema::connection($this->connection)->hasColumn('aldermen_recipients', 'slug')) {
            Schema::connection($this->connection)->table('aldermen_recipients', function (Blueprint $table): void {
                $table->dropColumn('slug');
            });
        }
    }
};
