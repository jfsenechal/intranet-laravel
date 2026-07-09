<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::connection('maria-meal-delivery')->hasColumn('meals', 'at_cafeteria')) {
            return;
        }

        Schema::connection('maria-meal-delivery')->table('meals', function (Blueprint $table): void {
            $table->boolean('at_cafeteria')->default(false)->change();
        });
    }

    public function down(): void
    {
        if (! Schema::connection('maria-meal-delivery')->hasColumn('meals', 'at_cafeteria')) {
            return;
        }

        Schema::connection('maria-meal-delivery')->table('meals', function (Blueprint $table): void {
            $table->boolean('at_cafeteria')->default(null)->change();
        });
    }
};
