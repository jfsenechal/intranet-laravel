<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The rename migration used Blueprint::removeColumn(), which only strips a column from the
 * pending blueprint and never emits an `alter table ... drop column`, so the legacy column
 * survived on already-migrated databases.
 */
return new class extends Migration
{
    protected $connection = 'maria-conseil';

    public function up(): void
    {
        if (! Schema::connection($this->connection)->hasColumn('agendas', 'date_fin_diffusion')) {
            return;
        }

        Schema::connection($this->connection)->table('agendas', function (Blueprint $table): void {
            $table->dropColumn('date_fin_diffusion');
        });
    }
};
