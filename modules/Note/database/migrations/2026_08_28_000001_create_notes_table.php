<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'maria-note';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-note');

        if ($schema->hasTable('note') && ! $schema->hasTable('notes')) {
            $schema->table('note', function (Blueprint $table): void {
                $table->rename('notes');
            });
        }

        if (! $schema->hasTable('notes')) {
            $schema->create('notes', function (Blueprint $table): void {
                $table->id();
                $table->string('name');
                $table->longText('content');
                $table->string('user_add');
                $table->timestamps();
            });

            return;
        }

        if (! $schema->hasColumn('notes', 'categorie_id')) {
            return;
        }

        $this->dropForeignKeysOnCategorieId();

        $schema->table('notes', function (Blueprint $table): void {
            $table->dropColumn('categorie_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-note')->dropIfExists('notes');
    }

    /**
     * Drop any foreign key defined on notes.categorie_id.
     *
     * The legacy organisation database carries a Doctrine-generated constraint on that
     * column (FK_CFBDFA14BCF5E72D at the time of writing) pointing at a categorie table
     * that no longer exists, and MariaDB refuses to drop the column while it stands.
     *
     * The hashed name must never be hardcoded, so it is read back from the schema.
     *
     * MariaDB only: SQLite cannot drop a foreign key by name and throws instead. That is
     * fine here, because the constraint only exists in the legacy organisation database
     * and the guard in up() never reaches this on a fresh install or the sqlite test
     * schema. Dropping by column list would work on both, but Laravel would then derive
     * the conventional name (notes_categorie_id_foreign), which is not what the legacy
     * database calls it.
     */
    private function dropForeignKeysOnCategorieId(): void
    {
        $schema = Schema::connection('maria-note');

        $names = collect($schema->getForeignKeys('notes'))
            ->filter(fn (array $foreignKey): bool => in_array('categorie_id', $foreignKey['columns'], true))
            ->pluck('name')
            ->all();

        if ($names === []) {
            return;
        }

        $schema->table('notes', function (Blueprint $table) use ($names): void {
            foreach ($names as $name) {
                $table->dropForeign($name);
            }
        });
    }
};
