<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::connection('maria-mileage')->hasTable('tarif')) {
            Schema::connection('maria-mileage')->table('tarif', function (Blueprint $table) {
                $table->rename('rates');
            });
            Schema::connection('maria-mileage')->table('rates', function (Blueprint $table) {
                $table->renameColumn('montant', 'amount');
                $table->renameColumn('date_debut', 'start_date');
                $table->renameColumn('date_fin', 'end_date');
            });
        } else {
            Schema::connection('maria-mileage')->create('rates', function (Blueprint $table) {
                $table->id();
                $table->decimal('amount', 10, 2);
                $table->decimal('omnium', 10, 2);
                $table->date('start_date')->unique();
                $table->date('end_date')->unique();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('maria-mileage')->dropIfExists('rates');
    }
};
