<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * `is_replacement` was carried over from the legacy schema as a `varchar(5)`
 * holding 'oui'/'non', which the `hrm:migration` command later rewrote to '1'/'0'.
 * The column is a flag like `is_closed`, `is_amendment` and `is_suspended`, all of
 * which are real booleans, and a string column makes every read a truthiness trap:
 * the legacy 'non' is a non-empty string, so it evaluates to true.
 *
 * Values are normalised before the type changes, because MariaDB in strict mode
 * refuses to cast a leftover 'oui' to an integer and would abort the ALTER.
 */
return new class extends Migration
{
    protected $connection = 'maria-hrm';

    public function up(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasColumn('contracts', 'is_replacement')) {
            return;
        }

        $contracts = DB::connection($this->connection)->table('contracts');

        (clone $contracts)
            ->whereIn('is_replacement', ['oui', 'true', 'yes'])
            ->update(['is_replacement' => '1']);

        (clone $contracts)
            ->where(function ($query): void {
                $query->whereNotIn('is_replacement', ['1'])
                    ->orWhereNull('is_replacement');
            })
            ->update(['is_replacement' => '0']);

        $schema->table('contracts', function (Blueprint $table): void {
            $table->boolean('is_replacement')->default(false)->change();
        });
    }

    public function down(): void
    {
        $schema = Schema::connection($this->connection);

        if (! $schema->hasColumn('contracts', 'is_replacement')) {
            return;
        }

        $schema->table('contracts', function (Blueprint $table): void {
            $table->string('is_replacement', 5)->default('0')->change();
        });
    }
};
