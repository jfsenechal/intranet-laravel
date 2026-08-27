<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const string INDEX = 'incoming_mails_department_reference_number_index';

    protected $connection = 'maria-courrier';

    /**
     * `IncomingMail::nextCpasReferenceNumber()` reads the highest CPAS
     * reference on every "Traiter" modal, and the `CAST(reference_number AS
     * SIGNED)` it needs makes the single-column `reference_number` index
     * unusable: the query fell back to a full scan of the whole table
     * (measured: 1.9s for 166k rows).
     *
     * A composite `(department, reference_number)` index covers it — the plan
     * becomes an index-only ref lookup on the department, so the cast runs over
     * the CPAS keys alone and never touches a row (measured: 9ms).
     */
    public function up(): void
    {
        if ($this->indexExists()) {
            return;
        }

        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            $table->index(['department', 'reference_number'], self::INDEX);
        });
    }

    public function down(): void
    {
        if (! $this->indexExists()) {
            return;
        }

        Schema::connection('maria-courrier')->table('incoming_mails', function (Blueprint $table): void {
            $table->dropIndex(self::INDEX);
        });
    }

    private function indexExists(): bool
    {
        return Schema::connection('maria-courrier')
            ->hasIndex('incoming_mails', self::INDEX);
    }
};
