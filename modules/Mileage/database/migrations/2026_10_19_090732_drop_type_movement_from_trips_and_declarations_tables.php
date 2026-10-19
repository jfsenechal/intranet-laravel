<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The type of movement (interne/externe) is no longer used. The columns are
     * kept for their history but made nullable, as the application stops
     * filling them.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-mileage');

        foreach (['trips', 'declarations'] as $tableName) {
            if (! $schema->hasColumn($tableName, 'type_movement')) {
                continue;
            }

            $schema->table($tableName, function (Blueprint $table): void {
                $table->string('type_movement')->nullable()->change();
                //   $table->dropColumn('type_movement');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-mileage');

        foreach (['trips', 'declarations'] as $tableName) {
            if (! $schema->hasColumn($tableName, 'type_movement')) {
                continue;
            }

            $schema->getConnection()
                ->table($tableName)
                ->whereNull('type_movement')
                ->update(['type_movement' => 'interne']);

            $schema->table($tableName, function (Blueprint $table): void {
                $table->string('type_movement')->nullable(false)->change();
            });
        }
    }
};
