<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::connection('maria-mileage')->create('tarif', function (Blueprint $table) {
            $table->id();
            $table->decimal('montant', 10, 2);
            $table->decimal('omnium', 10, 2);
            $table->date('date_debut')->unique();
            $table->date('date_fin')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('tarif');
    }
};
