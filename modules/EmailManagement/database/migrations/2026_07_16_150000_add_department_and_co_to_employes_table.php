<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the two identity attributes the legacy GestEmail form never carried.
 *
 * Both are near-universal in the directory -- department on 298 of 299 entries, co on 285 --
 * so the mirror was losing them on every sync.
 */
return new class extends Migration
{
    private const array COLUMNS = ['department', 'co'];

    protected $connection = 'maria-email-management';

    public function up(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($schema): void {
            foreach (self::COLUMNS as $column) {
                if (! $schema->hasColumn('employes', $column)) {
                    $table->string($column)->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-email-management');

        if (! $schema->hasTable('employes')) {
            return;
        }

        $existing = array_values(array_filter(
            self::COLUMNS,
            fn (string $column): bool => $schema->hasColumn('employes', $column),
        ));

        if ($existing === []) {
            return;
        }

        $schema->table('employes', function (Blueprint $table) use ($existing): void {
            $table->dropColumn($existing);
        });
    }
};
