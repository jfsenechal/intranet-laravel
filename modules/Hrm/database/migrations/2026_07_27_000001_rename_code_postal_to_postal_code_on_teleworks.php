<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The legacy `teletravail` table was renamed to `teleworks` and its French column
 * names translated, but `code_postal` was missed. The model and forms expect
 * `postal_code`, so every insert failed with "Unknown column 'postal_code'".
 */
return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasColumn('teleworks', 'code_postal') && ! $schema->hasColumn('teleworks', 'postal_code')) {
            $schema->table('teleworks', function (Blueprint $table): void {
                $table->renameColumn('code_postal', 'postal_code');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if ($schema->hasColumn('teleworks', 'postal_code') && ! $schema->hasColumn('teleworks', 'code_postal')) {
            $schema->table('teleworks', function (Blueprint $table): void {
                $table->renameColumn('postal_code', 'code_postal');
            });
        }
    }
};
