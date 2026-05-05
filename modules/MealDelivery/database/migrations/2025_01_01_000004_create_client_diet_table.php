<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('client_regime')) {
            Schema::connection('maria-meal-delivery')->table('client_regime', function (Blueprint $table): void {
                $table->rename('client_diet');
            });
            Schema::connection('maria-meal-delivery')->table('client_diet', function (Blueprint $table): void {
                $table->renameColumn('regime_id', 'diet_id');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('client_diet')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('client_diet', function (Blueprint $table): void {
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('diet_id')->constrained('diets')->cascadeOnDelete();
            $table->primary(['client_id', 'diet_id']);
        });
    }
};
