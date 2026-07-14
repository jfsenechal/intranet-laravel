<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The legacy `rates` table (renamed from `tarif`) has no timestamp columns,
     * so the Rate model's inserts fail on `updated_at`. Add them if missing.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-mileage');

        if ($schema->hasColumn('rates', 'created_at') && $schema->hasColumn('rates', 'updated_at')) {
            return;
        }

        $schema->table('rates', function (Blueprint $table) use ($schema): void {
            if (! $schema->hasColumn('rates', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }

            if (! $schema->hasColumn('rates', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-mileage');

        $columns = array_values(array_filter(
            ['created_at', 'updated_at'],
            fn (string $column): bool => $schema->hasColumn('rates', $column),
        ));

        if ($columns === []) {
            return;
        }

        $schema->table('rates', function (Blueprint $table) use ($columns): void {
            $table->dropColumn($columns);
        });
    }
};
