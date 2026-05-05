<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('commande')) {
            Schema::connection('maria-meal-delivery')->table('commande', function (Blueprint $table): void {
                $table->rename('orders');
            });
            Schema::connection('maria-meal-delivery')->table('orders', function (Blueprint $table): void {
                $table->renameColumn('semaine_id', 'week_id');
                $table->renameColumn('remarque', 'notes');
                $table->renameColumn('is_last_repas', 'is_last_meal');
                $table->renameColumn('createdAt', 'created_at');
                $table->renameColumn('updatedAt', 'updated_at');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('orders')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('orders', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('week_id')->nullable()->constrained('weeks')->nullOnDelete();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->boolean('is_last_meal')->default(false);
            $table->timestamps();
            $table->unique(['week_id', 'client_id']);
        });
    }
};
