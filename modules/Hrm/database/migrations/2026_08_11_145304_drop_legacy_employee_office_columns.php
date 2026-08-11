<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The rename migration used Blueprint::removeColumn(), which only strips a column from the
 * pending blueprint and never emits an `alter table ... drop column`, so the legacy columns
 * survived on already-migrated databases. The office phone numbers now live on
 * `professional_phone` and `professional_mobile`.
 *
 * `employeur_save_id` still carries the legacy Doctrine foreign key to `employers`, and
 * MariaDB refuses to drop a column whose index a constraint still needs, so the constraint
 * goes first. Its name is looked up rather than hard-coded because Doctrine derived it from
 * a table-name hash, and it is not guaranteed to match across deployments.
 */
return new class extends Migration
{
    /**
     * @var array<int, string>
     */
    private const LEGACY_COLUMNS = ['employeur_save_id', 'phone_office', 'mobile_office'];

    protected $connection = 'maria-hrm';

    public function up(): void
    {
        $columns = array_values(array_filter(
            self::LEGACY_COLUMNS,
            fn (string $column): bool => Schema::connection($this->connection)->hasColumn('employees', $column),
        ));

        if ($columns === []) {
            return;
        }

        $this->dropForeignKeysFor($columns);

        Schema::connection($this->connection)->table('employees', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }

    /**
     * @param  array<int, string>  $columns
     */
    private function dropForeignKeysFor(array $columns): void
    {
        $connection = DB::connection($this->connection);

        if ($connection->getDriverName() !== 'mariadb' && $connection->getDriverName() !== 'mysql') {
            return;
        }

        $constraints = $connection->select(
            'select distinct constraint_name from information_schema.key_column_usage
             where table_schema = database()
               and table_name = ?
               and referenced_table_name is not null
               and column_name in ('.implode(',', array_fill(0, count($columns), '?')).')',
            ['employees', ...$columns],
        );

        foreach ($constraints as $constraint) {
            Schema::connection($this->connection)->table(
                'employees',
                function (Blueprint $table) use ($constraint): void {
                    $table->dropForeign($constraint->constraint_name);
                }
            );
        }
    }
};
