<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-mileage')->table('rates', function (Blueprint $table): void {
            $table->decimal('amount', 10, 4)->change();
            $table->decimal('omnium', 10, 4)->change();
        });

        Schema::connection('maria-mileage')->table('trips', function (Blueprint $table): void {
            $table->decimal('rate', 10, 4)->nullable()->change();
            $table->decimal('omnium', 10, 4)->nullable()->change();
        });

        Schema::connection('maria-mileage')->table('declarations', function (Blueprint $table): void {
            $table->decimal('rate', 10, 4)->change();
            $table->decimal('rate_omnium', 10, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->table('rates', function (Blueprint $table): void {
            $table->decimal('amount', 10, 2)->change();
            $table->decimal('omnium', 10, 2)->change();
        });

        Schema::connection('maria-mileage')->table('trips', function (Blueprint $table): void {
            $table->decimal('rate', 10, 2)->nullable()->change();
            $table->decimal('omnium', 10, 2)->nullable()->change();
        });

        Schema::connection('maria-mileage')->table('declarations', function (Blueprint $table): void {
            $table->decimal('rate', 10, 2)->change();
            $table->decimal('rate_omnium', 10, 2)->change();
        });
    }
};
