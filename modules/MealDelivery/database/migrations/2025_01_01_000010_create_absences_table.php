<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::connection('maria-meal-delivery')->hasTable('absence')) {
            Schema::connection('maria-meal-delivery')->table('absence', function (Blueprint $table): void {
                $table->rename('delivery_absences');
            });
            Schema::connection('maria-meal-delivery')->table('delivery_absences', function (Blueprint $table): void {
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
            });
        }

        if (Schema::connection('maria-meal-delivery')->hasTable('delivery_absences')) {
            return;
        }
        Schema::connection('maria-meal-delivery')->create('delivery_absences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->unique()->constrained('clients')->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }
};
