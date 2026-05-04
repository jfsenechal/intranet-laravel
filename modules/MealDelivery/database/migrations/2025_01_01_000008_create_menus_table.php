<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('menu')) {
            Schema::connection('maria-meal-delivery')->table('menu', function (Blueprint $table): void {
                $table->rename('menus');
            });
            Schema::connection('maria-meal-delivery')->table('menus', function (Blueprint $table): void {
                $table->renameColumn('repas_id', 'meal_id');
                $table->renameColumn('quantite', 'quantity');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('menus')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('menus', function (Blueprint $table): void {
            $table->id();
            $table->integer('position');
            $table->integer('quantity')->default(0);
            $table->foreignId('meal_id')->constrained('meals')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('menus');
    }
};
