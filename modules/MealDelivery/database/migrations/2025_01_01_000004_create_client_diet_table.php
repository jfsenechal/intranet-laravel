<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-meal-delivery')->create('client_diet', function (Blueprint $table): void {
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('diet_id')->constrained('diets')->cascadeOnDelete();
            $table->primary(['client_id', 'diet_id']);
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('client_diet');
    }
};
