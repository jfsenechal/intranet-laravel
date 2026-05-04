<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('tournee')) {
            Schema::connection('maria-meal-delivery')->table('tournee', function (Blueprint $table): void {
                $table->rename('delivery_routes');
            });
            Schema::connection('maria-meal-delivery')->table('delivery_routes', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('delivery_routes')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('delivery_routes', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('delivery_routes');
    }
};
