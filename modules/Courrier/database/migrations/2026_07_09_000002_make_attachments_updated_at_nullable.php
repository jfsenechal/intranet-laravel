<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-courrier';

    /**
     * The `Attachment` model uses `$timestamps = false`, so inserts never set
     * `updated_at`. The legacy `attachement` table shipped this column as
     * `NOT NULL` with no default, which rejects those inserts (SQLSTATE 1364).
     * Make it nullable to match the model and the fresh-create migration.
     */
    public function up(): void
    {
        Schema::connection('maria-courrier')->table('attachments', function (Blueprint $table): void {
            $table->dateTime('updated_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-courrier')->table('attachments', function (Blueprint $table): void {
            $table->dateTime('updated_at')->nullable(false)->change();
        });
    }
};
