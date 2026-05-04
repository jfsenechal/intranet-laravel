<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('semaine')) {
            Schema::connection('maria-meal-delivery')->table('semaine', function (Blueprint $table): void {
                $table->rename('weeks');
            });
            Schema::connection('maria-meal-delivery')->table('weeks', function (Blueprint $table): void {
                $table->renameColumn('premier_jour', 'first_day');
                $table->renameColumn('jours', 'days');
                $table->renameColumn('remarque', 'notes');
                $table->renameColumn('archive', 'is_archived');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('weeks')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('weeks', function (Blueprint $table): void {
            $table->id();
            $table->date('first_day');
            $table->json('days')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('weeks');
    }
};
