<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Constrain `roles.module_id` to an existing module.
     *
     * The column was declared with `foreignIdFor()` and no `constrained()`, so
     * deleting a module left its roles pointing at a missing row. Deleting a
     * module should not delete roles that users still hold, so the reference is
     * nulled instead: the role survives, detached from any module.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->{self::columnTypeOf('modules')}('module_id')->nullable()->change();
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->foreign('module_id')
                ->references('id')
                ->on('modules')
                ->nullOnDelete();
        });
    }

    /**
     * Drop the foreign key and widen the column back to `bigint unsigned`.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table): void {
            $table->dropForeign(['module_id']);
        });

        Schema::table('roles', function (Blueprint $table): void {
            $table->unsignedBigInteger('module_id')->nullable()->change();
        });
    }

    /**
     * Blueprint method matching the type of the given table's `id` column.
     *
     * `modules` predates Laravel in the deployed database and still carries a
     * signed `int(11)` key, while a database built from scratch by these
     * migrations gets `$table->id()` (`bigint unsigned`). A foreign key needs
     * both sides to match exactly, so the real type is read at runtime.
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
