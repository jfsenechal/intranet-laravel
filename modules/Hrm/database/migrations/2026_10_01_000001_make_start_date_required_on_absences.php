<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-hrm';

    /**
     * An absence without a start date cannot be placed on a calendar, so the form
     * now requires one. The legacy rows that predate that rule are dropped: they
     * were all closed files kept for history only.
     */
    public function up(): void
    {
        DB::connection($this->connection)
            ->table('absences')
            ->whereNull('start_date')
            ->delete();

        Schema::connection($this->connection)->table('absences', function (Blueprint $table): void {
            $table->date('start_date')->nullable(false)->change();
        });
    }

    /**
     * Only the nullability is reversible; the deleted rows are not.
     */
    public function down(): void
    {
        Schema::connection($this->connection)->table('absences', function (Blueprint $table): void {
            $table->date('start_date')->nullable()->change();
        });
    }
};
