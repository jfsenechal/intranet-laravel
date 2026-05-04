<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('repas')) {
            Schema::connection('maria-meal-delivery')->table('repas', function (Blueprint $table): void {
                $table->rename('meals');
            });
            Schema::connection('maria-meal-delivery')->table('meals', function (Blueprint $table): void {
                $table->renameColumn('commande_id', 'order_id');
                $table->renameColumn('jour', 'date');
                $table->renameColumn('nb_potage', 'soup_count');
                $table->renameColumn('remarque', 'notes');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('meals')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('meals', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->integer('soup_count')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('at_cafeteria')->default(false);
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('maria-meal-delivery')->dropIfExists('meals');
    }
};
