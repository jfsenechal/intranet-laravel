<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trips no longer record their departure and arrival locations: every trip
     * is now an internal movement. The meal expense is kept.
     */
    public function up(): void
    {
        $schema = Schema::connection('maria-mileage');

        if (! $schema->hasColumn('trips', 'departure_location')) {
            return;
        }

        $schema->table('trips', function (Blueprint $table): void {
            //   $table->dropColumn(['departure_location', 'arrival_location']);
        });
    }

    public function down(): void
    {
        $schema = Schema::connection('maria-mileage');

        if ($schema->hasColumn('trips', 'departure_location')) {
            return;
        }

        $schema->table('trips', function (Blueprint $table): void {
            $table->string('departure_location')->nullable();
            $table->string('arrival_location')->nullable();
        });
    }
};
