<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('regime')) {
            Schema::connection('maria-meal-delivery')->table('regime', function (Blueprint $table): void {
                $table->rename('diets');
            });
            Schema::connection('maria-meal-delivery')->table('diets', function (Blueprint $table): void {
                $table->renameColumn('nom', 'name');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('diets')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('diets', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('not_deletable')->nullable()->default(false);
            $table->timestamps();
        });
    }
};
