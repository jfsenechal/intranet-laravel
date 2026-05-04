<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('maria-meal-delivery')->create('diets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('not_deletable')->nullable()->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('diets');
    }
};
