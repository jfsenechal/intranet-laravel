<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('menu_regime')) {
            Schema::connection('maria-meal-delivery')->table('menu_regime', function (Blueprint $table): void {
                $table->rename('diet_menu');
            });
            Schema::connection('maria-meal-delivery')->table('diet_menu', function (Blueprint $table): void {
                $table->renameColumn('regime_id', 'diet_id');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('diet_menu')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('diet_menu', function (Blueprint $table): void {
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('diet_id')->constrained('diets')->cascadeOnDelete();
            $table->primary(['menu_id', 'diet_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('diet_menu');
    }
};
