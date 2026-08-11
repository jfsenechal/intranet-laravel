<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The rename migration used Blueprint::removeColumn(), which only strips a column from the
 * pending blueprint and never emits an `alter table ... drop column`. The legacy
 * `sports_members.user` column therefore survived, and being `not null` without a default it
 * broke every member creation.
 */
return new class extends Migration
{
    protected $connection = 'maria-rescam';

    public function up(): void
    {
        if (! Schema::connection($this->connection)->hasColumn('sports_members', 'user')) {
            return;
        }

        Schema::connection($this->connection)->table('sports_members', function (Blueprint $table): void {
            $table->dropColumn('user');
        });
    }
};
