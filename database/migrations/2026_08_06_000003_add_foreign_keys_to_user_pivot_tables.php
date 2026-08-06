<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pivot columns and the table each one references.
     *
     * @var array<string, array<string, string>>
     */
    private const PIVOTS = [
        'role_user' => ['user_id' => 'users', 'role_id' => 'roles'],
        'module_user' => ['user_id' => 'users', 'module_id' => 'modules'],
        'module_user_favorites' => ['user_id' => 'users', 'module_id' => 'modules'],
    ];

    /**
     * Give the user pivot tables real foreign keys.
     *
     * The columns were declared with `foreignIdFor()` and no `constrained()`,
     * so deleting a user, role or module left its pivot rows behind. Cascading
     * deletes make the database enforce what the application assumed.
     *
     * Any surviving orphan is deleted first: MySQL refuses to create a foreign
     * key while rows violate it.
     */
    public function up(): void
    {
        foreach (self::PIVOTS as $pivot => $columns) {
            foreach ($columns as $column => $parent) {
                DB::table($pivot)
                    ->whereNotIn($column, DB::table($parent)->select('id'))
                    ->delete();
            }

            Schema::table($pivot, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column => $parent) {
                    $table->{self::columnTypeOf($parent)}($column)->change();
                }
            });

            Schema::table($pivot, function (Blueprint $table) use ($columns): void {
                foreach ($columns as $column => $parent) {
                    $table->foreign($column)
                        ->references('id')
                        ->on($parent)
                        ->cascadeOnDelete();
                }
            });
        }
    }

    /**
     * Drop the foreign keys and widen the columns back to `bigint unsigned`.
     * The rows deleted by `up()` are not restored.
     */
    public function down(): void
    {
        foreach (self::PIVOTS as $pivot => $columns) {
            Schema::table($pivot, function (Blueprint $table) use ($columns): void {
                foreach (array_keys($columns) as $column) {
                    $table->dropForeign([$column]);
                }
            });

            Schema::table($pivot, function (Blueprint $table) use ($columns): void {
                foreach (array_keys($columns) as $column) {
                    $table->unsignedBigInteger($column)->change();
                }
            });
        }
    }

    /**
     * Blueprint method matching the type of the given table's `id` column.
     *
     * MySQL requires both sides of a foreign key to have the same type and
     * signedness. `users` and `modules` predate Laravel in the deployed
     * database and still carry a signed `int(11)` key, while a database built
     * from scratch by these migrations gets `$table->id()` (`bigint unsigned`)
     * everywhere. Reading the real type keeps both cases working.
     */
    private static function columnTypeOf(string $parent): string
    {
        $column = collect(Schema::getColumns($parent))->firstWhere('name', 'id');
        $type = $column['type'] ?? 'bigint unsigned';
        $isUnsigned = str_contains($type, 'unsigned');

        return match (true) {
            in_array($column['type_name'] ?? '', ['int', 'integer'], true) => $isUnsigned ? 'unsignedInteger' : 'integer',
            $isUnsigned => 'unsignedBigInteger',
            default => 'bigInteger',
        };
    }
};
