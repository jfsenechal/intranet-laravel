<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A trip is a single day: its type of movement now only depends on the
     * departure and arrival locations, so the arrival date is no longer used.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-mileage');

        if (! $schema->hasColumn('trips', 'arrival_date')) {
            return;
        }

        $schema->table('trips', function (Blueprint $table): void {
            //   $table->dropColumn('arrival_date');
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-mileage');

        if ($schema->hasColumn('trips', 'arrival_date')) {
            return;
        }

        $schema->table('trips', function (Blueprint $table): void {
            $table->dateTime('arrival_date')->nullable()->after('departure_date');
        });
    }
};
